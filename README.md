# AsyncSQL
This library allows you to query databases without blocking the main thread. Especially for some game server, they've got a lots of information to fetch from the remote database. This library is also use to be substituted with PocketMine's native AsyncTask with long-term database connection.

## Third-party Libraries/Protocols Used
* __[PHP MySQLi](http://php.net/manual/en/book.mysqli.php)__
* __[PHP pthreads](http://pthreads.org/)__ by _[krakjoe](https://github.com/krakjoe)_: Threading for PHP - Share Nothing, Do Everything.