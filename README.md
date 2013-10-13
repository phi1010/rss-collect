rss-collect
===========

Scripts to collect data from other networks as rss feed.
Currently only Tumblr Dashboard as Beta supported.
Todo:
- Iterate over posts to show more than 20, compare timestamps.
- Check OAuth implementation and usage security. (This is just a working proof of concept without any security claimed.)
- Allow tokens to be provided via GET-Parameters to allow multiuser without database. (HTTPS recommended...) tumblr-auth-php would return an rss url with the tokens included. The app secret stays secret.
