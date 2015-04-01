<?php
namespace Task\ConsoleBundle\Services;

use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterService
{
    private $twitter = false;
    private $errorMsg = false;

    /**
     * Constructor of the TwitterService
     *
     * @param TwitterOAuth $twitterOauth TwitterOAuth Object initialized with the config parameters.
     * @param int $timeout The timeout of the twitter response
     */
    public function __construct(TwitterOAuth $twitterOauth, $timeout)
    {
        $this->twitter = $twitterOauth;
        $this->twitter->setTimeouts($timeout, $timeout);
        $this->twitter->setDecodeJsonAsArray(true);
    }

    /**
     * Get the last $limit tweets of a user
     *
     * @param string $screenName The account name which should be parsed
     * @param int $limit Number of tweets
     *
     * @return array|bool|object Return the response from TwitterOAuth (an array of tweets) or false if the $limit is 0 or false.
     */
    public function getTweetsByScreenName($screenName, $limit)
    {

        $tweets = false;
        if (!empty($limit)) {
            $tweets = $this->twitter->get('statuses/user_timeline', array('screen_name' => $screenName, 'count' => $limit, 'trim_user' => 'true', 'exclude_replies' => 'true', 'include_rts' => 'false'));
            //catch twitter api authentication errors
            if (!empty($tweets['errors'])) {
                $this->parseAuthenticationError($tweets['errors']);
                $tweets = false;
            } //catch twitter api request/response errors
            else if (!empty($tweets['error'])) {
                $this->parseRequestError($tweets);
                $tweets = false;
            }
        }
        return $tweets;
    }

    /**
     * Count the keywords from the tweets
     *
     * @param array $tweets The list of tweets
     * @param bool $sort Boolean says if the result should be ordered DESC or not.
     *
     * @return array|bool An array of counted keywords from the extracted tweets
     */
    public function countTweetsKeywords($tweets, $sort = false)
    {
        $words = false;
        if (is_array($tweets)) {
            foreach ($tweets as $tweet) {
                //extract the words from the current tweet
                $tweetWords = $keywords = preg_split("/[\s,!;\?]+/", $tweet['text']);
                //count each word
                foreach ($tweetWords as $word) {
                    $word = trim($word);
                    if (!empty($word)) {
                        $words[$word] = empty($words[$word]) ? 1 : $words[$word] + 1;
                    }
                }
            }
            //sort the array (DESC) if this is required.
            if ($sort && is_array($words)) {
                arsort($words);
            }
        }

        return $words;
    }

    /**
     * Parse the list of authentication errors
     *
     * @param array $errors The list of errors
     */
    private function parseAuthenticationError($errors)
    {
        if (is_array($errors)) {
            foreach ($errors as $error) {
                $this->errorMsg .= 'Error code: ' . $error['code'] . "\r\n" . 'Error message: ' . $error['message'] . "\r\n";
            }
        }
    }

    /**
     * Parse the list of the twitter request/response error
     *
     * @param array $error The error details array
     */
    private function parseRequestError($error)
    {
        $this->errorMsg .= !empty($error['request']) ? 'The Twitter request ' . $error['request'] . ' failed! ' . "\r\n" : false;
        $this->errorMsg .= !empty($error['error']) ? 'The response message is: ' . $error['error'] . "\r\n" : false;
    }

    /**
     * Returns the error message or false f it's empty
     *
     * @return bool|string
     */
    public function getError()
    {
        return $this->errorMsg;
    }
}