<?php
namespace Task\ConsoleBundle\Tests\Services;

use Task\ConsoleBundle\Services\TwitterService;
use Prophecy\Prophet;

class TwitterServiceTest extends \PHPUnit_Framework_TestCase
{

    protected $twitter;
    protected $tweets;
    protected $screenName = 'Secretsales';
    protected $limit = 2;
    protected $timeout = 20;
    protected $twitterProphecy;

    protected function setup()
    {
        $twitterCommand = 'statuses/user_timeline';
        $twitterParams = array('screen_name' => $this->screenName, 'count' => $this->limit, 'trim_user' => 'true', 'exclude_replies' => 'true', 'include_rts' => 'false');
        $tweetsJson = '[{"created_at":"Sun Mar 29 05:59:57 +0000 2015","id":582059601870393344,"id_str":"582059601870393344","text":"Give your wardrobe essentials a spring clean with luxury underwear from @CalvinKlein. Shop now &gt;&gt; http:\/\/t.co\/xHho9xMlzF","source":"\u003ca href=\"http:\/\/www.conversocial.com\" rel=\"nofollow\"\u003eConversocial\u003c\/a\u003e","truncated":false,"in_reply_to_status_id":null,"in_reply_to_status_id_str":null,"in_reply_to_user_id":null,"in_reply_to_user_id_str":null,"in_reply_to_screen_name":null,"user":{"id":14480686,"id_str":"14480686"},"geo":null,"coordinates":null,"place":null,"contributors":null,"retweet_count":0,"favorite_count":0,"entities":{"hashtags":[],"symbols":[],"user_mentions":[{"screen_name":"CalvinKlein","name":"Calvin Klein","id":18647765,"id_str":"18647765","indices":[72,84]}],"urls":[{"url":"http:\/\/t.co\/xHho9xMlzF","expanded_url":"http:\/\/bit.ly\/1HS7Fbg","display_url":"bit.ly\/1HS7Fbg","indices":[104,126]}]},"favorited":false,"retweeted":false,"possibly_sensitive":false,"lang":"en"},{"created_at":"Sat Mar 28 20:42:57 +0000 2015","id":581919427836416000,"id_str":"581919427836416000","text":"Got the touch of death with plants? Say hello to greener fingers with #TheSecret Planting 101 http:\/\/t.co\/JkbnDmAerA http:\/\/t.co\/MK22BcDHrZ","source":"\u003ca href=\"http:\/\/www.conversocial.com\" rel=\"nofollow\"\u003eConversocial\u003c\/a\u003e","truncated":false,"in_reply_to_status_id":null,"in_reply_to_status_id_str":null,"in_reply_to_user_id":null,"in_reply_to_user_id_str":null,"in_reply_to_screen_name":null,"user":{"id":14480686,"id_str":"14480686"},"geo":null,"coordinates":null,"place":null,"contributors":null,"retweet_count":0,"favorite_count":0,"entities":{"hashtags":[{"text":"TheSecret","indices":[70,80]}],"symbols":[],"user_mentions":[],"urls":[{"url":"http:\/\/t.co\/JkbnDmAerA","expanded_url":"http:\/\/bit.ly\/19UyTA1","display_url":"bit.ly\/19UyTA1","indices":[94,116]}],"media":[{"id":581919427618344960,"id_str":"581919427618344960","indices":[117,139],"media_url":"http:\/\/pbs.twimg.com\/media\/CBNkouMXEAAEjxW.jpg","media_url_https":"https:\/\/pbs.twimg.com\/media\/CBNkouMXEAAEjxW.jpg","url":"http:\/\/t.co\/MK22BcDHrZ","display_url":"pic.twitter.com\/MK22BcDHrZ","expanded_url":"http:\/\/twitter.com\/Secretsales\/status\/581919427836416000\/photo\/1","type":"photo","sizes":{"medium":{"w":600,"h":399,"resize":"fit"},"small":{"w":340,"h":226,"resize":"fit"},"thumb":{"w":150,"h":150,"resize":"crop"},"large":{"w":1024,"h":681,"resize":"fit"}}}]},"extended_entities":{"media":[{"id":581919427618344960,"id_str":"581919427618344960","indices":[117,139],"media_url":"http:\/\/pbs.twimg.com\/media\/CBNkouMXEAAEjxW.jpg","media_url_https":"https:\/\/pbs.twimg.com\/media\/CBNkouMXEAAEjxW.jpg","url":"http:\/\/t.co\/MK22BcDHrZ","display_url":"pic.twitter.com\/MK22BcDHrZ","expanded_url":"http:\/\/twitter.com\/Secretsales\/status\/581919427836416000\/photo\/1","type":"photo","sizes":{"medium":{"w":600,"h":399,"resize":"fit"},"small":{"w":340,"h":226,"resize":"fit"},"thumb":{"w":150,"h":150,"resize":"crop"},"large":{"w":1024,"h":681,"resize":"fit"}}}]},"favorited":false,"retweeted":false,"possibly_sensitive":false,"lang":"en"}]';
        $this->tweets = json_decode($tweetsJson, true);

        //create prophecy (Mocks) for TwitterOAuth
        $prophet = new Prophet;
        $twitterProphecy = $prophet->prophesize('Abraham\TwitterOAuth\TwitterOAuth');
        $twitterProphecy->setTimeouts($this->timeout, $this->timeout)->shouldBeCalled();
        $twitterProphecy->setDecodeJsonAsArray(true)->shouldBeCalled();
        $twitterProphecy->get($twitterCommand, $twitterParams)->willReturn($this->tweets);
        $twitterOAuth = $twitterProphecy->reveal();
        $this->twitterProphecy = $twitterProphecy;

        $this->twitter = new TwitterService($twitterOAuth, $this->timeout);
    }

    public function testGetTweetsByScreenName()
    {
        $tweets = $this->twitter->getTweetsByScreenName($this->screenName, $this->limit);

        // assert that returned tweets from the getTweetsByScreenName method are equal with the mocked ones.
        $this->assertEquals($this->tweets, $tweets);
    }

    public function testZeroLimit()
    {
        $tweets = $this->twitter->getTweetsByScreenName($this->screenName, 0);

        $this->assertEquals(false, $tweets);
    }

    public function testCountTweetsKeywords()
    {
        $keywords = $this->twitter->countTweetsKeywords($this->tweets);

        $this->assertEquals(32, count($keywords));
    }

    public function testEmptyTweetsKeywords()
    {
        $keywordsFalse = $this->twitter->countTweetsKeywords(false);
        $keywordsEmpty = $this->twitter->countTweetsKeywords(array());

        $this->assertEquals(false, $keywordsFalse);
        $this->assertEquals(false, $keywordsEmpty);
    }

    public function testSortedCountTweetsKeywords()
    {
        $keywords = $this->twitter->countTweetsKeywords($this->tweets, true);

        $prevValue = false;
        foreach($keywords as $keyword => $count)
        {
            if(!empty($prevValue)) {
                $this->assertGreaterThanOrEqual($count, $prevValue);
            }
            $prevValue = $count;
        }
    }

    public function testAuthenticationError()
    {
        $errorMessage = 'Could not authenticate you.';
        $errorCode = 32;
        $authenticationError = array('errors' => array(
            array(
                'code' => $errorCode,
                'message' => $errorMessage
            )
        ));
        $expectedErrorString = "Error code: ".$errorCode."\r\nError message: ".$errorMessage."\r\n";

        //create prophecy (Mocks) for authentication error
        $twitterCommand = 'statuses/user_timeline';
        $twitterParams = array('screen_name' => $this->screenName, 'count' => $this->limit, 'trim_user' => 'true', 'exclude_replies' => 'true', 'include_rts' => 'false');

        $this->twitterProphecy->get($twitterCommand, $twitterParams)->willReturn($authenticationError);
        $twitterOAuth = $this->twitterProphecy->reveal();

        $twitter = new TwitterService($twitterOAuth, $this->timeout);
        $tweets = $twitter->getTweetsByScreenName($this->screenName, $this->limit);
        $error = $twitter->getError();

        $this->assertEquals(false, $tweets);
        $this->assertEquals($expectedErrorString, $error);
    }

    public function testRequestError()
    {
        $requestString = '/1.1/statuses/user_timeline.json?count='.$this->limit.'&exclude_replies=true&include_rts=false&screen_name='.$this->screenName.'&trim_user=true';
        $errorMessage = 'Not authorized.';
        $requestError = array(
            'request' => $requestString,
            'error' => $errorMessage
        );
        $expectedErrorString = "The Twitter request ".$requestString." failed! \r\nThe response message is: ".$errorMessage."\r\n";

        //create prophecy (Mocks) for authentication error
        $twitterCommand = 'statuses/user_timeline';
        $twitterParams = array('screen_name' => $this->screenName, 'count' => $this->limit, 'trim_user' => 'true', 'exclude_replies' => 'true', 'include_rts' => 'false');

        $this->twitterProphecy->get($twitterCommand, $twitterParams)->willReturn($requestError);
        $twitterOAuth = $this->twitterProphecy->reveal();

        $twitter = new TwitterService($twitterOAuth, $this->timeout);
        $tweets = $twitter->getTweetsByScreenName($this->screenName, $this->limit);
        $error = $twitter->getError();

        $this->assertEquals(false, $tweets);
        $this->assertEquals($expectedErrorString, $error);
    }

}