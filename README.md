<p align="center">
    <a href="https://fluprint.com" target="_blank">
        <img width="200" src="https://github.com/LogIN-/fluprint.com/raw/master/static/logo.png"></a>
</p>

<p align="center">
	<a href="https://app.fossa.io/projects/git%2Bgithub.com%2FLogIN-%2Ffluprint?ref=badge_shield" alt="FOSSA Status">
		<img src="https://app.fossa.io/api/projects/git%2Bgithub.com%2FLogIN-%2Ffluprint.svg?type=shield"/></a>
    <a href="#reposize">
        <img src="https://img.shields.io/github/repo-size/LogIN-/fluprint.svg" /></a>
    <a href="https://twitter.com/intent/follow?screen_name=TomicAdriana" alt="Follow me on twitter">
        <img src="https://img.shields.io/twitter/follow/TomicAdriana.svg?label=Follow&style=social&logo=twitter" alt="Follow me on twitter"></a>
</p>

> Welcome to backend interface and import build script for FluPRINT database

### Technology
`FluPRINT` uses a number of open source projects to work properly ¯\_(ツ)_/¯
You can find more info on [fluprint.com](fluprint.com) website that is also avaliable as open-source project [here](https://github.com/LogIN-/fluprint.com)

### Installation process
To install and configure `FluPRINT` first you need to satisfy following requirements: `PHP > 7`, `MySQL`, `Linux` or `Mac OS`.

Please make sure to install those basic dependencies and start the `MySQL` server before proceeding to installation.

#### Installation Quickstart

1. Install composer
```sh
	php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
	php -r "if (hash_file('sha384', 'composer-setup.php') === '93b54496392c062774670ac18b134c3b3a95e5a5e5c8f1a9f115f203b75bf9a129d5daa8ba6a13e2cc8a1da0806388a8') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
	php composer-setup.php
	php -r "unlink('composer-setup.php');"
```

2. Install PHP dependencies by running following command from project root directory: 
```sh
	composer install
```

3. Create new MySQL database and configure MySQL user

4. Now when dependencies are set please import database schema into MySQL database you created

```bash
	mysql -u username -p database_name < ./documentation/fluprint_schema.sql
```

5. Adjust database user credentials by editing following file:
```bash
	./config/configuration.json
```

6. Download raw data from the [here](https://zenodo.org/record/3213899#.XOb9dqR7lPY), make new directory `./data/upload` place your data inside in following format
```sh
	./data/upload/{STUDY_ID}/*.csv
```
STUDY_ID must be a number that is already mapped inside this file `./config/configuration.json`
Currently supported ones are: 30, 29, 28, 24, 21, 22, 18, 17, 15

7. Start the import and import all data into database!
```sh
	php bin/import.php -t import
```

## Submitting Bugs and Enhancements
[GitHub Issues](https://github.com/LogIN-/fluprint/issues) is for suggesting enhancements and reporting bugs. We appreciate all enhancements ideas and bug reports. Additionally if you think you can help us with suggesting new useful features we will gladly accept it.

## How to use this dataset
One of the examples how to use this dataset is described in our [publication](https://www.biorxiv.org/content/10.1101/545186v1)
Publication code with some examples can also be found as an open source project [here](https://github.com/LogIN-/simon-manuscript)

### Other useful resources
You may also find helpful our other open source projects

* [mulset](https://github.com/LogIN-/mulset) - Multi-set intersection R package
* [simon](https://github.com/genular/simon-frontend) - Automated knowledge discovery platform

## Reaching Out
If you'd like to start a conversation feel free to e-mail me at [atomic@stanford.edu](mailto:atomic@stanford.edu)
We would also gladly like to hear from you if you find this project useful and helpful.

## License
Please check `LICENSE` file for more information.
The Software is provided "as is", without warranty of any kind.

## Citation

If you use our code for research, please cite following publications:

```sh
Adriana Tomic, Ivan Tomic, Cornelia L Dekker, Holden T Maecker, Mark M Davis
bioRxiv 564062; doi: https://doi.org/10.1101/564062
```
```sh
Adriana Tomic, & Ivan Tomic. (2019). Raw data for the generation of the FluPRINT dataset [Data set]. Zenodo. https://doi.org/10.5281/zenodo.3213899
```

Any questions? Please contact us at [atomic@stanford.edu](mailto:atomic@stanford.edu)