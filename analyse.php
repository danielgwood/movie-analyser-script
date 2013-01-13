<?php

/**
 * Movie Analyser
 * Extracts some information from the stats generated by collate.php (run this first!)
 * 
 * @author Daniel G Wood <https://github.com/danielgwood>
 */

$json = file_get_contents('movies.json');
if(!$json) {
    echo "movies.json not found! Please run collate.php.\n\n";
}
$data = json_decode($json);

// Totals
echo "\n" . $data->totalMovies . " in your collection, with a total running time of " . $data->totalRunningTime . " minutes, that's " . round((($data->totalRunningTime / 60) / 24), 1) . " days!\n";

// Oldest/newest
echo "\n\nOldest film: " . $data->oldestFilm->title . " (" . date("Y", $data->oldestFilm->date) . ")";
echo "\nNewest film: " . $data->newestFilm->title . " (" . date("Y", $data->newestFilm->date) . ")";

// Best/worst
echo "\n\nBest film: " . $data->bestFilm->title . " (" . $data->bestFilm->rating . "/10)";
echo "\nWorst film: " . $data->worstFilm->title . " (" . $data->worstFilm->rating . "/10)";

// Genres
$genres = (array)$data->genres;

arsort($genres);
echo "\n\nFavourite genres:\n";
for($i = 0; $i < 10; $i++) {
    echo key($genres) . " (" . current($genres) . ")\n";
    next($genres);
}

reset($genres);
asort($genres);
echo "\n\nLeast-favourite genres:\n";
for($i = 0; $i < 10; $i++) {
    echo key($genres) . " (" . current($genres) . ")\n";
    next($genres);
}

// Ratings
$averageRating = round(array_sum((array)$data->ratings) / $data->totalMovies, 2);
echo "\nAverage rating: {$averageRating}\n";

// Summarise directors
$directors = (array)$data->directors;
arsort($directors);
echo "\nFavourite directors:\n";
for($i = 0; $i < 10; $i++) {
    echo key($directors) . " (" . current($directors) . ")\n";
    next($directors);
}

// Summarise cast
$cast = (array)$data->cast;
arsort($cast);
echo "\nFavourite actors/actresses:\n";
for($i = 0; $i < 10; $i++) {
    echo key($cast) . " (" . current($cast) . ")\n";
    next($cast);
}