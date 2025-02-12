<?php

declare(strict_types=1);

namespace BeastBytes\View\Latte\Form\Node;

use BeastBytes\View\Latte\Form\Config\ConfigTrait;
use Generator;
use Latte\CompileException;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\Php\IdentifierNode;
use Latte\Compiler\Nodes\Php\Scalar\NullNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

final class GroupNode extends StatementNode
{
    use ConfigTrait;

    private IdentifierNode $name;
    private ExpressionNode $theme;

    /**
     * @throws CompileException
     */
    public static function create(Tag $tag): self
    {
        $tag->expectArguments();
        $node = $tag->node = new self;
        $node->name = new IdentifierNode($tag->name);
        $node->theme = new NullNode();

        foreach ($tag->parser->parseArguments() as $argument) {
            $node->theme = $argument->value;
        }

        $node->config = $tag->parser->parseModifier();

        return $node;
    }

    public function print(PrintContext $context): string
    {
        return $context->format(
            <<<'MASK'
            echo Yiisoft\FormModel\Field::%node(%raw, %node) %line;
            echo "\n";
            MASK,
            $this->name,
            $this->getConfig($context),
            $this->theme,
            $this->position,
        );
    }

    /**
     * @inheritDoc
     */
    public function &getIterator(): Generator
    {
        yield $this->name;
        yield $this->theme;
    }
}