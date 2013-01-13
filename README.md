movie-analyser
==============

A PHP script to take a list of films and collate some stats, such as popular genres, actors, directors, eras and ratings.

Requirements
------------
* PHP 5.2.x or higher
* cURL
* TMDb API-key

Usage
-----
1. Acquire an API key for TMDb, following the instructions on http://api.themoviedb.org/2.1/ (it's usually pretty fast).
2. Put API key into `collate.php` in the constants.
3. Get a big ol' list of films, and provide them one per line to `collate.php` via `stdin`.
    * An example list (`movies.txt`) is provided
    * The script will use the best match, so if you have an ambiguous title, add the year in brackets, for example
      "True Grit (2010)".
    * For best results, use the US title of the film
4. Once done, a `movies.json` file will be output with all the generated statistics
5. You can run this through `analyse.php` to get an overview, or do something else entirely with the data!

Fork me!
--------
I'm particularly interested to see any additional stats generated, but bugfixes and optimisations are also welcome. For me it was important to get something together quickly so I could spend more time making pretty infographics with the output ;)

You can find out more about the API methods available here: http://docs.themoviedb.apiary.io/

Remember that anything you develop using this code should abide by the TMDb terms and conditions.

Thanks
------
* TheMovieDb.org for the excellent free data.
* Jonas De Smet (glamorous) for the TMDb API class.

License
-------
TMDb.php is under the BSD license, as provided by glamorous: https://github.com/glamorous/TMDb-PHP-API
