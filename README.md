
# Sidecampus

Sidecampus is a free open-source web project that aims to enhance collaborative work among students, as well as be their own personal companion. 

Students can register and join so-called "platforms", which represent either a campus, a class or a group of students. 
Each registered student can at least have a private agenda, exam-session manager, file system and a to-do list. 
There are also collaborative file spaces and agendas per platform, thus helping collaborative work. 

This open-source web application can be easily deployed on any web server with a SQL database following installation rules described below. 

## Getting Started


### Prerequisites

You will need:
- A running web server, like Apache or Nginx.
- PHP mailing enabled and configured.
- A running SQL database. MySQL is recommended.
- A domain name 

### Installing and deployment

1. Be sure that your web server is up and running
2. Be sure that your SQL databse is up and running and accessible from your web server 
3. Upload your code to your home web folder
4. Execute the database/db_schema.sql file from your database 
5. Configure your application through /config/application.ini (see details here below)
6. Write your terms of use in the View/CGU.html
7. Add the cron/unblocker file to your crontab, or disable IP Blocker in your configuration file
8. Test it!
9. Securize your files. Web user should not have access to subfolders.

### Configuration

| Key | Default value | Description |
| --- | --- | --- |
| website.title | My Sidecampus | Title of your main website used in the webapp and in the emails. You should change it. |
| website.url | https://www.mydomain.com | URL of your main website. Don't put an "/" at the end |
| website.logo | media/pics/logo-long-fond-bleu.png | Location from the root folder of the website main logo |
| noreply.email | no-reply@mydomain.com | Email used for every email sent by the platform. |
| contact.email | info@mydomain.com | Email which webapp users can use to contact a webmaster or an administrator. |
| audit.email | audit@mydomain.com | Recipient for all automatic doain auditing procedures. |
| enable.platformcreation | 1 | Whether you want to allow every registered user to create a platform or not. Should be 0 or 1. |
| enable.ipblocker | 1 | Whether you want to activate the ip blocker option (for security purposes). Should be 0 or 1. |
| db.host | localhost | IP or domain where your database is located. |
| db.name | sidecampus | Database name. |
| db.user | root | Database user for webapp connection. This should be changed. |
| db.password | | Password of database user. This should be changed. |
| upload.max_file_size | 100 | Maximum size a registered user can upload. |
| download.max_file_size | 100 | Maximum size a registered user can download. |

### Code structure
The projet uses a custom lightweight web framework under the MVC design pattern. Every basic request goes trough "request.php", which is the principal entry point for the webapp. Several other entry points are present: ajax.php for Ajax requests, dfi.php for file displays, dim.php for image displays, gview.php for file previews. 

#### Controllers
Controllers are situated under the "Control" folder. A central controller - PageController - dynamically calls the right Controller depending on "action" parameter in URI. Every controller implements a "BodyController" interface. 

#### Models
Models are situated under the "Model" folder. They all use the DB class, access point to the database

#### Views
Views are situated under the "View" folder. They are all templates. It uses a system of "markers" that have a "###MARKER###" structure. These markers are interpreted by a GenerateUtils class called from the controllers. Nothing but HTML should be used here. 
Available structural markers are:
* ###XXX###: variable, replaced by passed value. 
* ###IF_XXX###: for conditional display. 
* ###SUB_XXX###: to define a subsection of the template. It is replaced by passed value.
* ###RS_XXX###: resultset marker, allows to directly inject a resultset in an html template.

#### Other folders
* Utils: several utilitaries classes.
* config: contains configuration files
* cron: contains routine scripts that can be added to a crontab
* database: sql scripts, contains the installation script
* css: all css files, plain css, not compiled
* js: all javascript files. 
* dev: development utils. Most useful one is the generate.php that allows to easily add new controllers, models,...
* media: static images and user files. 

## About

### Version
Current version is 1.0.

### Contributing

Please read [CONTRIBUTING.md](https://github.com/videony/sidecampus/blob/master/CONTRIBUTING.md) for details on our code of conduct, and the process for submitting pull requests to us.

### Authors

* **John Mannard** - *Initial work* - [http://mannard.com/](http://mannard.com/)
* **François Thiébaut** - *Contributions to initial work*

See also the list of [contributors](https://github.com/videony/sidecampus/contributors) who participated in this project.

### License

This project is licensed under the Mozilla Public License - see the [LICENSE.md](https://github.com/videony/sidecampus/blob/master/LICENSE) file for details

### Acknowledgments

* Unamur: for prooftesting this webapp.
