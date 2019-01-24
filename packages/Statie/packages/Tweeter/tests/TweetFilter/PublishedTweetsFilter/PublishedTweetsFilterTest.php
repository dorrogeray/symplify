<?php declare(strict_types=1);

namespace Symplify\Statie\Tweeter\Tests\TweetFilter\PublishedTweetsFilter;

use Symplify\Statie\Configuration\StatieConfiguration;
use Symplify\Statie\Tests\AbstractContainerAwareTestCase;
use Symplify\Statie\Tweeter\TweetFilter\PublishedTweetsFilter;
use Symplify\Statie\Tweeter\TweetProvider\PostTweetsProvider;

final class PublishedTweetsFilterTest extends AbstractContainerAwareTestCase
{
    /**
     * @var PublishedTweetsFilter
     */
    private $publishedTweetsFilter;

    /**
     * @var PostTweetsProvider
     */
    private $postTweetsProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $configuration = $this->container->get(StatieConfiguration::class);
        $configuration->setSourceDirectory(__DIR__ . '/../../Source');

        $this->postTweetsProvider = $this->container->get(PostTweetsProvider::class);
        $this->publishedTweetsFilter = $this->container->get(PublishedTweetsFilter::class);
    }

    public function test(): void
    {
        if (getenv('TWITTER_CONSUMER_KEY') === false) {
            $this->markTestSkipped('Run Twitter test only with access tokens.');
        }

        $postTweets = $this->postTweetsProvider->provide();
        $this->assertCount(1, $postTweets);

        $unpublishedTweets = $this->publishedTweetsFilter->filter($postTweets);

        foreach ($unpublishedTweets as $unpublishedTweet) {
            // this tweet is already published, so it should not be here
            $this->assertNotContains(
                'New post on my blog: Clean and Decoupled Controllers, Commands and Event Subscribers ',
                $unpublishedTweet->getText()
            );
        }

        $this->assertCount(1, $unpublishedTweets);
    }
}
