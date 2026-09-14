<?php

namespace App\Core;

use RuntimeException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * A rejected form, carrying one or more messages per field.
 *
 * Both kinds of validation end up here: the shape checks the schema declares,
 * and the domain checks a service runs against the database. The form does not
 * care which one failed, it just prints what came back for each field.
 */
class ValidationException extends RuntimeException
{
    /** @var array<string, string[]> Field name => messages. */
    private array $errors;

    /** @param array<string, string[]> $errors */
    public function __construct(array $errors, ?\Throwable $previous = null)
    {
        $this->errors = $errors;

        parent::__construct($this->firstMessage() ?? 'Validation failed', 0, $previous);
    }

    /**
     * For a single rule that a schema cannot express, such as a uniqueness
     * check. Takes the driver error as $previous when the constraint in the
     * database is what caught it, so the original is not lost.
     */
    public static function forField(string $field, string $message, ?\Throwable $previous = null): self
    {
        return new self(array($field => array($message)), $previous);
    }

    /**
     * A nested schema reports paths like "user.dni", but the input in the form
     * is just named "dni". Field names are unique across a schema and the ones
     * it nests, so the last segment identifies the input on its own.
     */
    public static function fromViolations(ConstraintViolationListInterface $violations): self
    {
        $errors = array();

        foreach ($violations as $violation) {
            $path = $violation->getPropertyPath();
            $position = strrpos($path, '.');
            $field = $position === false ? $path : substr($path, $position + 1);

            $errors[$field][] = (string) $violation->getMessage();
        }

        return new self($errors);
    }

    /** @return array<string, string[]> */
    public function errors(): array
    {
        return $this->errors;
    }

    public function has(string $field): bool
    {
        return !empty($this->errors[$field]);
    }

    public function first(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    /** The message shown at the top of the form when it is rejected. */
    public function firstMessage(): ?string
    {
        foreach ($this->errors as $messages) {
            if ($messages) {
                return $messages[0];
            }
        }

        return null;
    }
}
