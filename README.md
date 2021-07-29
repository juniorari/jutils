# JuniorAri Utils

Class for methods and lot of stuff utilities

## Getting Started

Execute command line:
```
$ composer require juniorari/jutils
```
Or add on your require section of your **composer.json**
```
"juniorari/jutils": "stable"
```

### Prerequisities
- PHP >= 7.0
- Extension PHP mb_string enabled
- Extension PHP curl enabled

On Windows, locate and edit **php.ini** file, and uncomment line:
```
extension=mbstring
extension=curl
```

On Linux, run command (where X is a version of your PHP):
```
$ sudo apt-get install php7.X-mbstring
$ sudo apt-get install php7.X-curl
```

## How to use
```
<?php
use JuniorAri\Utils\JUtils;
$utils = new JUtils();
echo $utils->capitalizeName("josé antônio da silva");
```
or
```
echo JUtils::capitalizeName("josé antônio da silva");
```

## Functions:
Function  | What       |
----------|------------|
capitalizeName  | Capitalize a string
toUpper         | Convert to uppercase 
toLower         | Convert to lowercase
removeAccents   | Remove accenst from string                        
createAlias     | Create an alias
searchCEP       | Search a brazilian CEP                             
dateToday       | Return today date
dateIsValid     | Check if is valide an date on specified format    



@github: github.com:juniorari/jutils 

<!--

Explain how to run the automated tests for this system

### Break down into end to end tests

Explain what these tests test and why

```
Give an example
```

### And coding style tests

Explain what these tests test and why

```
Give an example
```

## Deployment

Add additional notes about how to deploy this on a live system

## Built With

* Dropwizard - Bla bla bla
* Maven - Maybe
* Atom - ergaerga

## Contributing

Please read [CONTRIBUTING.md](https://gist.github.com/PurpleBooth/b24679402957c63ec426) for details on our code of conduct, and the process for submitting pull requests to us.

## Versioning

We use [SemVer](http://semver.org/) for versioning. For the versions available, see the [tags on this repository](https://github.com/your/project/tags). 

## Authors

* **Billie Thompson** - *Initial work* - [PurpleBooth](https://github.com/PurpleBooth)

See also the list of [contributors](https://github.com/your/project/contributors) who participated in this project.

## License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details

## Acknowledgments

* Hat tip to anyone who's code was used
* Inspiration
* etc
-->