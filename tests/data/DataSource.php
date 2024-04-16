<?php

namespace yiiunit\extensions\graphql\data;

/**
 * Class DataSource
 *
 * This is just a simple in-memory data holder for the sake of example.
 * Data layer for real app may use Doctrine or query the database directly (e.g. in CQRS style)
 *
 * @package GraphQL\Examples\Blog
 */
class DataSource
{
    private static array $users = [];

    private static array $stories = [];

    private static $storyLikes = [];

    private static array $comments = [];

    private static $storyComments = [];

    private static $commentReplies = [];

    private static $storyMentions = [];

    public static function init()
    {
        self::$users = [
            '1' => new User(
                [
                    'id' => '1',
                    'email' => 'john@example.com',
                    'email2' => 'john2@example.com',
                    'firstName' => 'John',
                    'lastName' => 'Doe',
                    'password' => '123456',
                ]
            ),
            '2' => new User(
                [
                    'id' => '2',
                    'email' => 'jane@example.com',
                    'email2' => 'john2@example.com',
                    'firstName' => 'Jane',
                    'lastName' => 'Doe',
                    'password' => '123456',
                ]
            ),
            '3' => new User(
                [
                    'id' => '3',
                    'email' => 'john@example.com',
                    'email2' => 'john2@example.com',
                    'firstName' => 'John',
                    'lastName' => 'Doe',
                    'password' => '123456',
                ]
            ),
        ];

        self::$stories = [
            '1' => new Story(['id' => '1', 'authorId' => '1', 'body' => '<h1>GraphQL is awesome!</h1>']),
            '2' => new Story(['id' => '2', 'authorId' => '1', 'body' => '<a>Test this</a>']),
            '3' => new Story(['id' => '3', 'authorId' => '3', 'body' => "This\n<br>story\n<br>spans\n<br>newlines"]),
        ];

        self::$storyLikes = [
            '1' => ['1', '2', '3'],
            '2' => [],
            '3' => ['1']
        ];

        self::$comments = [
            // thread #1:
            '100' => new Comment(['id' => '100', 'authorId' => '3', 'storyId' => '1', 'body' => 'Likes']),
            '110' => new Comment(['id' => '110', 'authorId' => '2', 'storyId' => '1', 'body' => 'Reply <b>#1</b>', 'parentId' => '100']),
            '111' => new Comment(['id' => '111', 'authorId' => '1', 'storyId' => '1', 'body' => 'Reply #1-1', 'parentId' => '110']),
            '112' => new Comment(['id' => '112', 'authorId' => '3', 'storyId' => '1', 'body' => 'Reply #1-2', 'parentId' => '110']),
            '113' => new Comment(['id' => '113', 'authorId' => '2', 'storyId' => '1', 'body' => 'Reply #1-3', 'parentId' => '110']),
            '114' => new Comment(['id' => '114', 'authorId' => '1', 'storyId' => '1', 'body' => 'Reply #1-4', 'parentId' => '110']),
            '115' => new Comment(['id' => '115', 'authorId' => '3', 'storyId' => '1', 'body' => 'Reply #1-5', 'parentId' => '110']),
            '116' => new Comment(['id' => '116', 'authorId' => '1', 'storyId' => '1', 'body' => 'Reply #1-6', 'parentId' => '110']),
            '117' => new Comment(['id' => '117', 'authorId' => '2', 'storyId' => '1', 'body' => 'Reply #1-7', 'parentId' => '110']),
            '120' => new Comment(['id' => '120', 'authorId' => '3', 'storyId' => '1', 'body' => 'Reply #2', 'parentId' => '100']),
            '130' => new Comment(['id' => '130', 'authorId' => '3', 'storyId' => '1', 'body' => 'Reply #3', 'parentId' => '100']),
            '200' => new Comment(['id' => '200', 'authorId' => '2', 'storyId' => '1', 'body' => 'Me2']),
            '300' => new Comment(['id' => '300', 'authorId' => '3', 'storyId' => '1', 'body' => 'U2']),

            # thread #2:
            '400' => new Comment(['id' => '400', 'authorId' => '2', 'storyId' => '2', 'body' => 'Me too']),
            '500' => new Comment(['id' => '500', 'authorId' => '2', 'storyId' => '2', 'body' => 'Nice!']),
        ];

        self::$storyComments = [
            '1' => ['100', '200', '300'],
            '2' => ['400', '500']
        ];

        self::$commentReplies = [
            '100' => ['110', '120', '130'],
            '110' => ['111', '112', '113', '114', '115', '116', '117'],
        ];

        self::$storyMentions = [
            '1' => [
                self::$users['2']
            ],
            '2' => [
                self::$stories['1'],
                self::$users['3']
            ]
        ];
    }

    public static function findUser($id)
    {
        return self::$users[$id] ?? null;
    }

    public static function findStory($id)
    {
        return self::$stories[$id] ?? null;
    }

    public static function findComment($id)
    {
        return self::$comments[$id] ?? null;
    }

    public static function findLastStoryFor($authorId)
    {
        $storiesFound = array_filter(
            self::$stories,
            static fn(Story $story) => $story->authorId == $authorId
        );
        return $storiesFound === [] ? null : $storiesFound[count($storiesFound) - 1];
    }

    public static function findLikes($storyId, $limit)
    {
        $likes = self::$storyLikes[$storyId] ?? [];
        $result = array_map(
            static fn($userId) => self::$users[$userId],
            $likes
        );
        return array_slice($result, 0, $limit);
    }

    public static function isLikedBy($storyId, $userId)
    {
        $subscribers = self::$storyLikes[$storyId] ?? [];
        return in_array($userId, $subscribers);
    }

    public static function getUserPhoto($userId, $size)
    {
        return new Image(
            [
                'id' => $userId,
                'type' => Image::TYPE_USERPIC,
                'size' => $size,
                'width' => random_int(100, 200),
                'height' => random_int(100, 200)
            ]
        );
    }

    public static function findLatestStory()
    {
        return array_pop(self::$stories);
    }

    public static function findStories($limit, $afterId = null)
    {
        $start = $afterId ? (int) array_search($afterId, array_keys(self::$stories), true) + 1 : 0;
        return array_slice(array_values(self::$stories), $start, $limit);
    }

    public static function findComments($storyId, $limit = 5, $afterId = null)
    {
        $storyComments = self::$storyComments[$storyId] ?? [];

        $start = isset($after) ? (int) array_search($afterId, $storyComments, true) + 1 : 0;
        $storyComments = array_slice($storyComments, $start, $limit);

        return array_map(
            static fn($commentId) => self::$comments[$commentId],
            $storyComments
        );
    }

    public static function findReplies($commentId, $limit = 5, $afterId = null)
    {
        $commentReplies = self::$commentReplies[$commentId] ?? [];

        $start = isset($after) ? (int) array_search($afterId, $commentReplies, true) + 1 : 0;
        $commentReplies = array_slice($commentReplies, $start, $limit);

        return array_map(
            static fn($replyId) => self::$comments[$replyId],
            $commentReplies
        );
    }

    public static function countComments($storyId)
    {
        return isset(self::$storyComments[$storyId]) ? count(self::$storyComments[$storyId]) : 0;
    }

    public static function countReplies($commentId)
    {
        return isset(self::$commentReplies[$commentId]) ? count(self::$commentReplies[$commentId]) : 0;
    }

    public static function findStoryMentions($storyId)
    {
        return self::$storyMentions[$storyId] ?? [];
    }
}
