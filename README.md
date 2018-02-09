# Simple Insta Features
Simple Instagram clone with basic features
Build as a side project, to have a personal instagram for our friend circle
- Home Feed with all images from all users
- As a user, I can register an account, sign in, and sign out
- As a signed in user, I can upload an image
- As a signed in users, I can delete an image that I uploaded
- As a signed in user I can comment on images and delete my comments.
- One page website, uses REST API for database queries.

# Host on your own server with PHP language, MySQL server
- **Note**: This website is meant to be hosted on apache web server with `AllOverride All` setting to make redirects work.
- fork it
- create required database and tables (or simply import `simple-insta.sql` to your mysql server) on your mySQL server, it's better to use a new user with access to only this database.
- update database host url, database name, username and password in `/api/vi/config.php` (remove .template from filename)
- make sure `/api/v1/uploads` has public write access (`chmod 666`)
- make sure `/api/vi/config.php` has owner read-write, group read, public deny access (`chmod 640`)
- host it and you are good to go
