<?php

namespace Laratusk\SvgConverter\Exceptions;

use Illuminate\Contracts\Process\ProcessResult;
use RuntimeException;
use Throwable;

class SvgConverterException extends RuntimeException
{
    public function __construct(
        string $message = '',
        public readonly string $output = '',
        public readonly string $errorOutput = '',
        public readonly int $exitCode = 1,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $exitCode, $previous);
    }

    /**
     * Create an exception from a failed process result.
     */
    public static function fromProcess(ProcessResult $result, string $command = '', string $provider = 'SVG converter'): self
    {
        $message = trim($result->errorOutput()) ?: "{$provider} process failed.";

        if ($command !== '' && $command !== '0') {
            $message = "{$provider} command failed [{$command}]: {$message}";
        }

        return new self(
            message: $message,
            output: $result->output(),
            errorOutput: $result->errorOutput(),
            exitCode: $result->exitCode(),
        );
    }

    /**
     * Get a summary string for debugging.
     */
    public function getSummary(): string
    {
        return implode("\n", array_filter([
            "Exit code: {$this->exitCode}",
            $this->errorOutput !== '' && $this->errorOutput !== '0' ? "Stderr: {$this->errorOutput}" : null,
            $this->output !== '' && $this->output !== '0' ? "Stdout: {$this->output}" : null,
        ]));
    }
}
