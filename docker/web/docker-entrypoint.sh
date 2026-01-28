#!/bin/sh
set -e

if [ "$WORKER" = true ]; then
	echo "Worker mode detected, starting messenger consumer..."
    exec php bin/console messenger:consume --all -vv --time-limit=60 --limit=10 --memory-limit=128M
fi

if [ "$1" = 'frankenphp' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
	if [ "$APP_ENV" = 'dev' ]; then
	    # composer install
	fi

	if [ "$APP_ENV" = 'dev' ] && ! { [ -f config/jwt/private.pem ] && [ -f config/jwt/public.pem ]; }; then
        php bin/console lexik:jwt:generate-keypair --overwrite --quiet
    fi

	if [ "$APP_ENV" != 'dev' ]; then
	    if [ -z "$APP_VERSION" ]; then
	        echo "APP_VERSION is missing"
	        exit 1
        fi
        if [ -z "$SYMFONY_DECRYPTION_SECRET" ]; then
	        echo "SYMFONY_DECRYPTION_SECRET is missing"
	        exit 1
        fi
	    php bin/console secrets:decrypt-to-local --force
	    composer dump-env $APP_ENV
        composer run-script --no-dev post-install-cmd
	fi

	# Display information about the current project
	# Or about an error in project initialization
	php bin/console -V

	echo 'Waiting for database to be ready...'
	ATTEMPTS_LEFT_TO_REACH_DATABASE=60
	until [ $ATTEMPTS_LEFT_TO_REACH_DATABASE -eq 0 ] || DATABASE_ERROR=$(php bin/console dbal:run-sql -q "SELECT 1" 2>&1); do
		if [ $? -eq 255 ]; then
			# If the Doctrine command exits with 255, an unrecoverable error occurred
			ATTEMPTS_LEFT_TO_REACH_DATABASE=0
			break
		fi
		sleep 1
		ATTEMPTS_LEFT_TO_REACH_DATABASE=$((ATTEMPTS_LEFT_TO_REACH_DATABASE - 1))
		echo "Still waiting for database to be ready... Or maybe the database is not reachable. $ATTEMPTS_LEFT_TO_REACH_DATABASE attempts left."
	done

	if [ $ATTEMPTS_LEFT_TO_REACH_DATABASE -eq 0 ]; then
		echo 'The database is not up or not reachable:'
		echo "$DATABASE_ERROR"
		exit 1
	else
		echo 'The database is now ready and reachable'
	fi

	if [ "$(find ./migrations -iname '*.php' -print -quit)" ] && [ "$APP_ENV" != 'dev' ]; then
		php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing
	fi

	echo 'PHP app ready!'
fi

exec docker-php-entrypoint "$@"
