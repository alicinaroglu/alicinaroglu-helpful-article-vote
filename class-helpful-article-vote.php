<?php
// Define a class to handle article helpfulness voting


class Helpful_Article_Vote {

    // Constructor method to hook into WordPress at certain points
    public function __construct() {
        // Enqueue necessary scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

        // Define AJAX actions for submitting votes
        add_action('wp_ajax_submit_vote', array($this, 'submit_vote'));
        add_action('wp_ajax_nopriv_submit_vote', array($this, 'submit_vote'));

        // Append the voting buttons to the end of the content
        add_action('the_content', array($this, 'append_vote_buttons_to_content'));

        // Add a meta box to the post editing screen to show vote results
        add_action('add_meta_boxes', array($this, 'add_voting_meta_box'));
    }

    // Method to enqueue styles and scripts
    public function enqueue_scripts() {
        // Load the plugin's CSS file
        wp_enqueue_style('helpful-article-vote-css', plugins_url('/css/style.css', __FILE__));
        // Load the main JavaScript file with jQuery as a dependency
        wp_enqueue_script('helpful-article-vote-js', plugins_url('/js/main.js', __FILE__), array('jquery'), null, true);
        // Enqueue FontAwesome for icons
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css');
        // Localize script for AJAX requests, providing a nonce for security
        wp_localize_script('helpful-article-vote-js', 'ajax_object', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('vote_nonce')
        ));
    }

    // AJAX callback for submitting a vote
    public function submit_vote() {
        // Check for nonce security first
        $nonce = $_POST['security'] ?? '';
        if (!wp_verify_nonce($nonce, 'vote_nonce')) {
            wp_send_json_error('Nonce verification failed');
            return; // Stop execution if the nonce check fails
        }

        // Process the submitted vote
        $post_id = $_POST['post_id'] ?? 0;
        $vote = $_POST['vote'] ?? '';

        // Check if the user has already voted to prevent duplicate votes
        if ($this->has_user_voted($post_id)) {
            wp_send_json_error('You have already voted on this article.');
            return; // Stop execution if the user has voted
        }

        // Update the post's vote counts based on the user's vote
        $this->update_vote_results($post_id, $vote);

        // Calculate the new vote percentages and send them back to the user
        $positive_percentage = $this->calculate_percentage($post_id);
        $negative_percentage = 100 - $positive_percentage;

        wp_send_json_success(array(
            'new_positive_percentage' => $positive_percentage,
            'new_negative_percentage' => $negative_percentage
        ));
    }

    // Method to append voting buttons to post content
    public function append_vote_buttons_to_content($content) {
        global $post; // Use the global post object

        // Static variable to ensure buttons are only added once per post
        static $buttons_appended = array();

        // Append buttons if we're on a single post and in the main query loop
        if (is_single() && in_the_loop() && is_main_query()) {
            // Prevent adding buttons multiple times for the same post
            if (!isset($buttons_appended[$post->ID])) {
                $content .= $this->get_voting_buttons_html();
                $buttons_appended[$post->ID] = true;
            }
        }

        return $content; // Return the modified content
    }
    
  // Helper method to generate HTML for voting buttons

  private function get_voting_buttons_html() {
    global $post;

    $user_vote = $this->get_user_vote( $post->ID );
    $user_has_voted = $this->has_user_voted( $post->ID );
    $disabled_attr = $user_has_voted ? ' disabled' : '';

    $yes_voted_class = ( $user_vote === 'yes' ) ? ' user-voted' : '';
    $no_voted_class = ( $user_vote === 'no' ) ? ' user-voted' : '';

    $positive_percentage = $this->calculate_percentage( $post->ID );
    $negative_percentage = 100 - $positive_percentage;

    // Construct the vote prompt and button texts based on voting status.
    $vote_prompt = $user_has_voted ? 'THANK YOU FOR YOUR FEEDBACK.' : 'WAS THIS ARTICLE HELPFUL?';
    $yes_text = $user_has_voted ? '' : 'YES';
    $no_text = $user_has_voted ? '' : 'NO';

    // Construct the HTML for the voting buttons, showing only percentages if the user has voted.
    $html = '
        <div class="voting-section">
            <p class="vote-question">' . $vote_prompt . '</p>
            <div class="vote-buttons" data-postid="' . esc_attr( $post->ID ) . '">
                <button class="vote-button' . $yes_voted_class . '"' . $disabled_attr . ' data-vote="yes"><span class="vote-icon"><i class="fa fa-smile"></i></span><span class="vote-percentage">' . ( $user_has_voted ? $positive_percentage . '%' : $yes_text ) . '</span></button>
                <button class="vote-button' . $no_voted_class . '"' . $disabled_attr . ' data-vote="no"><span class="vote-icon"><i class="fa fa-frown"></i></span><span class="vote-percentage">' . ( $user_has_voted ? $negative_percentage . '%' : $no_text ) . '</span></button>
            </div>
        </div>
    ';

    return $html;
  }

  // Helper method to retrieve the user's vote from cookies
  private function get_user_vote( $post_id ) {
    // Check if a cookie is set for the user's vote on this post
    return $_COOKIE[ 'voted_' . $post_id ] ?? null;
  }

  // Helper method to calculate the percentage of positive votes
  private function calculate_percentage( $post_id ) {
    $positive_votes = ( int )get_post_meta( $post_id, 'positive_votes', true );
    $negative_votes = ( int )get_post_meta( $post_id, 'negative_votes', true );
    $total_votes = $positive_votes + $negative_votes;
    return $total_votes > 0 ? round( ( $positive_votes / $total_votes ) * 100 ) : 0;

  }

  // Updates the voting results based on the user's vote
  private function update_vote_results( $post_id, $vote ) {
    // Increment the appropriate vote count based on the vote
    if ( $vote === 'yes' ) {
      $current_votes = ( int )get_post_meta( $post_id, 'positive_votes', true );
      update_post_meta( $post_id, 'positive_votes', $current_votes + 1 );
    } elseif ( $vote === 'no' ) {
      $current_votes = ( int )get_post_meta( $post_id, 'negative_votes', true );
      update_post_meta( $post_id, 'negative_votes', $current_votes + 1 );
    }

    // Retrieve the IP address of the voter
    $user_ip = $_SERVER[ 'REMOTE_ADDR' ];

    // If the user hasn't voted before, add their IP to the list and set a cookie
    if ( !$this->has_user_voted( $post_id ) ) {
      // Add the IP address to the list of voters for this post
      $voted_ips = get_post_meta( $post_id, 'voted_ips', true ) ? : [];
      $voted_ips[] = $user_ip;
      update_post_meta( $post_id, 'voted_ips', $voted_ips );

      // Set a cookie to track the user's vote for 1 year
      setcookie( 'voted_' . $post_id, $vote, time() + 365 * 24 * 60 * 60, COOKIEPATH, COOKIE_DOMAIN, is_ssl() );
    }
  }

  // Adds a meta box to the admin post edit screen to display voting results
  public function add_voting_meta_box() {
    // Register the meta box for displaying voting results
    add_meta_box(
      'voting_results_meta_box', // Unique ID of the meta box
      __( 'Voting Results', 'your-text-domain' ), // Screen reader text for the box title
      array( $this, 'display_voting_meta_box' ), // Callback function to render the box contents
      'post', // Post type where the box should appear
      'side', // The context within the screen where the box should display
      'high' // Priority within the context where the box should show
    );
  }

  // Displays the voting results meta box content
  public function display_voting_meta_box( $post ) {
    // Retrieve the vote counts from the post meta
    $positive_votes = get_post_meta( $post->ID, 'positive_votes', true ) ? : 0;
    $negative_votes = get_post_meta( $post->ID, 'negative_votes', true ) ? : 0;

    // Display the vote counts
    echo '<p>Positive Votes: ' . esc_html( $positive_votes ) . '</p>';
    echo '<p>Negative Votes: ' . esc_html( $negative_votes ) . '</p>';
  }

  // Checks whether the current user has already voted on the post
  private function has_user_voted( $post_id ) {
    // Get the visitor's IP address
    $user_ip = $_SERVER[ 'REMOTE_ADDR' ];
    // Retrieve the list of IPs that have voted on this post
    $voted_ips = get_post_meta( $post_id, 'voted_ips', true ) ? : [];

    // Check if this IP has already voted
    return in_array( $user_ip, $voted_ips );
  }


}

// Instantiate the class to ensure the plugin runs
$helpful_article_vote = new Helpful_Article_Vote();