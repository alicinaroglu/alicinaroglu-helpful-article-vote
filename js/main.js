jQuery(document).ready(function($) {
    // Apply highlight to previously voted options
    $('.vote-button.user-voted').addClass('highlight-vote');

    // Event handler for clicking on vote buttons
    $('.vote-button').on('click', function(e) {
        e.preventDefault();

        var post_id = $(this).closest('.vote-buttons').data('postid');
        var vote = $(this).data('vote');
        var voteContainer = $(this).closest('.vote-buttons');
        var buttons = voteContainer.find('.vote-button');

        // Disable the buttons after the vote to prevent multiple submissions
        buttons.prop('disabled', true);

        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data: {
                action: 'submit_vote',
                post_id: post_id,
                vote: vote,
                security: ajax_object.nonce
            },
            // Inside the AJAX success callback
            success: function(response) {
                if (response.success) {
                    // Update the UI to reflect the vote
                    var voteTitle = voteContainer.siblings('.vote-question'); // Assuming .vote-question is the class for the title
                    voteTitle.text('THANK YOU FOR YOUR FEEDBACK.'); // Update the title text

                    var yesButton = voteContainer.find('button[data-vote="yes"]');
                    var noButton = voteContainer.find('button[data-vote="no"]');

                    // Update percentages
                    yesButton.find('.vote-percentage').text(response.data.new_positive_percentage + '%');
                    noButton.find('.vote-percentage').text(response.data.new_negative_percentage + '%');

                    buttons.removeClass('highlight-vote user-voted'); // Remove classes from all buttons
                    if(vote === 'yes') {
                        yesButton.addClass('highlight-vote user-voted'); // Add classes to the voted button
                    } else {
                        noButton.addClass('highlight-vote user-voted'); // Add classes to the voted button
                    }

                    // Disable all buttons since the user has now voted
                    buttons.prop('disabled', true);
                } else {
                    // If there was an error, re-enable the buttons
                    buttons.prop('disabled', false);
                    alert(response.data);
                }
            }

        });
    });
});
