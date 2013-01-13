<?php

/**
 * Movie Analyser
 * Given a list of films, looks them up on TheMovieDatabase to tell you
 * about what kind of films you like to watch.
 * 
 * Takes input from stdin, errors are output to stdout,
 * any data gleaned will go to ./movies.json
 * 
 * @author Daniel G Wood <https://github.com/danielgwood>
 */

// TMDb API class, courtesy of Jonas De Smet
// https://github.com/glamorous/TMDb-PHP-API
require 'TMDb.php';

// API configuration
define('TMDB_API_KEY', 'TODO_FILL_ME_IN'); // Get from http://api.themoviedb.org/2.1/
define('API_REQUEST_DELAY', 1);                             // Pause in secs between movie lookups to avoid flooding

// Stats to collate
$stats = array(
    'totalMovies' => 0,
    'totalRunningTime' => 0,
    'oldestFilm' => null,
    'newestFilm' => null,
    'bestFilm' => null,
    'worstFilm' => null,
    'genres' => array(),
    'releaseDates' => array(),
    'ratings' => array(),
    'directors' => array(),
    'cast' => array()
);

// Read in movies
$movieTitles = array();
$in = fopen('php://stdin', 'r');
while(!feof($in)){
    $title = cleanupTitle(fgets($in, 4096));
    if(strlen($title) > 0) {
        $movieTitles[] = $title;
    }
}

try {
    // Check input
    if(count($movieTitles) < 1) {
        throw new Exception('You must provide at least one movie to look up!');
    }
    
    // Setup a connection to TMDb
    $tmdb = new TMDb(TMDB_API_KEY);

    // Look each movie up in TMDb
    $unrecognisedMovies = array();
    foreach($movieTitles as $movieTitle) {
        
        $searchResult = $tmdb->searchMovie($movieTitle);
        if($searchResult && isset($searchResult['results']) && count($searchResult['results']) > 0) {
            // Found a film, yeah!
            $movieId = $searchResult['results'][0]["id"];
            
            // Get detailed info
            $movieDetails = $tmdb->getMovie($movieId);
            $movieCast = $tmdb->getMovieCast($movieId);
            
            // Begin collating stats
            $releaseDate = strtotime($movieDetails['release_date']);
            
            $stats['totalMovies']++;
            $stats['totalRunningTime'] += (int)$movieDetails['runtime'];
            
            if(!$stats['oldestFilm'] || $stats['oldestFilm']['date'] > $releaseDate) {
                $stats['oldestFilm'] = array(
                    'date' => $releaseDate,
                    'rating' => $movieDetails['vote_average'],
                    'title' => $movieDetails['title'],
                    'poster' => $movieDetails['poster_path']
                );
            }
            if(!$stats['newestFilm'] || $stats['newestFilm']['date'] < $releaseDate) {
                $stats['newestFilm'] = array(
                    'date' => $releaseDate,
                    'rating' => $movieDetails['vote_average'],
                    'title' => $movieDetails['title'],
                    'poster' => $movieDetails['poster_path']
                );
            }
            
            if($movieDetails['vote_count'] !== 0) {
                if(!$stats['bestFilm'] || $stats['bestFilm']['rating'] < $movieDetails['vote_average']) {
                    $stats['bestFilm'] = array(
                        'date' => $releaseDate,
                        'rating' => $movieDetails['vote_average'],
                        'title' => $movieDetails['title'],
                        'poster' => $movieDetails['poster_path']
                    );
                }
                if(!$stats['worstFilm'] || $stats['worstFilm']['rating'] > $movieDetails['vote_average']) {
                    $stats['worstFilm'] = array(
                        'date' => $releaseDate,
                        'rating' => $movieDetails['vote_average'],
                        'title' => $movieDetails['title'],
                        'poster' => $movieDetails['poster_path']
                    );
                }
            }
            
            foreach($movieDetails['genres'] as $genre) {
                $genreName = $genre['name'];
                
                if(!isset($stats['genres'][$genreName])) {
                    $stats['genres'][$genreName] = 0;
                }
                $stats['genres'][$genreName]++;
            }
            
            $stats['releaseDates'][] = $releaseDate;
            $stats['ratings'][] = $movieDetails['vote_average'];
            
            if(isset($movieCast['crew'])) {
                foreach($movieCast['crew'] as $crewMember) {
                    if($crewMember['job'] != 'Director') {
                        continue;
                    }
                    
                    if(!isset($stats['directors'][$crewMember['name']])) {
                        $stats['directors'][$crewMember['name']] = 0;
                    }
                    $stats['directors'][$crewMember['name']]++;
                }
            }
            
            if(isset($movieCast['cast'])) {
                foreach($movieCast['cast'] as $castMember) {
                    if(!isset($stats['cast'][$castMember['name']])) {
                        $stats['cast'][$castMember['name']] = 0;
                    }
                    $stats['cast'][$castMember['name']]++;
                }
            }
            
        } else {
            // No matches at all for this film
            $unrecognisedMovies[] = $movieTitle;
        }
        
        // Indicate progress and sleep a while before next movie
        echo '.';
        sleep(API_REQUEST_DELAY);
    }
    
    // Go back and convert poster URLs (saves a few requests)
    if(isset($stats['oldestFilm']['poster'])) {
        $stats['oldestFilm']['poster'] = $tmdb->getImageUrl($stats['oldestFilm']['poster'], TMDb::IMAGE_POSTER, 'w92');
    }
    if(isset($stats['newestFilm']['poster'])) {
        $stats['newestFilm']['poster'] = $tmdb->getImageUrl($stats['newestFilm']['poster'], TMDb::IMAGE_POSTER, 'w92');
    }
    if(isset($stats['bestFilm']['poster'])) {
        $stats['bestFilm']['poster'] = $tmdb->getImageUrl($stats['bestFilm']['poster'], TMDb::IMAGE_POSTER, 'w92');
    }
    if(isset($stats['worstFilm']['poster'])) {
        $stats['worstFilm']['poster'] = $tmdb->getImageUrl($stats['worstFilm']['poster'], TMDb::IMAGE_POSTER, 'w92');
    }
    
    // Done!
    file_put_contents('movies.json', json_encode($stats));
    echo "\nFinished! Collated stats on " . $stats['totalMovies'] . " movie(s). Output in 'movies.json'.\n";
    
    // Any movies not found?
    if(count($unrecognisedMovies) > 0) {
        throw new Exception("\nThe following movies were not found:\n" . implode("\n", $unrecognisedMovies));
    }
    
} catch(\Exception $e) {
    // Probably API issue
    echo $e->getMessage() . "\n";
}


/**
 * Helper function to clean up input
 * @param string $title
 * @return string
 */
function cleanupTitle($title)
{
    return trim($title);
}