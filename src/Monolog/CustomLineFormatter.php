<?php

namespace App\Monolog;

class CustomLineFormatter extends \Monolog\Formatter\LineFormatter
{
    public function __construct(?string $format = null, ?string $dateFormat = null, bool $allowInlineLineBreaks = false, bool $ignoreEmptyContextAndExtra = false, bool $includeStacktraces = false)
    {
        $dateFormat = 'Y-m-d H:i:s';
        parent::__construct($format, $dateFormat, $allowInlineLineBreaks, $ignoreEmptyContextAndExtra, $includeStacktraces);
    }
}