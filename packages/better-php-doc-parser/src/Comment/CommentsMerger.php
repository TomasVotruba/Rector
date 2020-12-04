<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Comment;

use PhpParser\Node;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class CommentsMerger
{
    /**
     * @param Node[] $mergedNodes
     */
    public function keepComments(Node $newNode, array $mergedNodes): void
    {
        $comments = $newNode->getComments();

        foreach ($mergedNodes as $mergedNode) {
            $comments = array_merge($comments, $mergedNode->getComments());
        }

        if ($comments === []) {
            return;
        }

//        $comments = array_merge($newNode->getComments(), $comments);
        $newNode->setAttribute(AttributeKey::COMMENTS, $comments);

        // remove so comments "win"
        $newNode->setAttribute(AttributeKey::PHP_DOC_INFO, null);
    }

    public function keepParent(Node $newNode, Node $oldNode): void
    {
        $parent = $oldNode->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent === null) {
            return;
        }

        $arrayPhpDocInfo = $parent->getAttribute(AttributeKey::PHP_DOC_INFO);
        $arrayComments = $parent->getComments();

        if ($arrayPhpDocInfo === null && $arrayComments === []) {
            return;
        }

        $newNode->setAttribute(AttributeKey::PHP_DOC_INFO, $arrayPhpDocInfo);
        $newNode->setAttribute(AttributeKey::COMMENTS, $arrayComments);
    }
}
