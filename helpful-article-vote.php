<?php
/**
 * Plugin Name: Was This Article Helpful?
 * Description: Allows users to vote on articles and see an average percentage of positive votes.
 * Version: 1.0
 * Author: Ali Çınaroğlu
 */

// Prevent direct access to the file.
defined('ABSPATH') or die('No script kiddies please!');

// Include the class file.
require_once plugin_dir_path(__FILE__) . 'class-helpful-article-vote.php';

// Instantiate the class to set everything in motion.
$helpful_article_vote = new Helpful_Article_Vote();
