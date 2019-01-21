# FluPRINT

### Tech
`FluPRINT` uses a number of open source projects to work properly ¯\_(ツ)_/¯


### Installation

FluPRINT requires PHP > 7, MySQL
Install the dependencies and start the mysql server.

* Install composer
```sh
	php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
	php -r "if (hash_file('sha384', 'composer-setup.php') === '93b54496392c062774670ac18b134c3b3a95e5a5e5c8f1a9f115f203b75bf9a129d5daa8ba6a13e2cc8a1da0806388a8') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
	php composer-setup.php
	php -r "unlink('composer-setup.php');"
```

* Install project PHP dependencies: 
```sh
	composer install
```

* Import database schema into MySQL
```sh
	## ./documentation/fluprint_schema.sql

	## Adjust database user credentials
	## ./config/configuration.json
```

* Download project data from the link provided and place it into
```sh
	./data/upload
```

* Import all database data
```sh
	php bin/import.php -t import
```

## License

This program is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.