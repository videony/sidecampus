
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

### About

## Version
Current version is 1.0.

## Contributing

Please read [CONTRIBUTING.md](https://gist.github.com/PurpleBooth/b24679402957c63ec426) for details on our code of conduct, and the process for submitting pull requests to us.

## Authors

* **John Mannard** - *Initial work* - [http://mannard.com/](http://mannard.com/)
* **François Thiébaut** - *Contributions to initial work*

See also the list of [contributors](https://github.com/videony/sidecampus/contributors) who participated in this project.

## License

This project is licensed under the Mozilla Public License - see the [LICENSE.md](LICENSE.md) file for details

## Acknowledgments

* Unamur: for prooftesting this webapp.