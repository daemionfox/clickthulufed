<?php

namespace App\Traits;

trait RecursiveCopyTrait
{

    protected function recurseCopy($source, $dest): bool
    {
        if (is_file($source)) {
            return copy($source, $dest);
        }
        if (!is_dir($dest)) {
            mkdir($dest);
        }
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            if ($dest !== "$source/$entry") {
                $this->recurseCopy("$source/$entry", "$dest/$entry");
            }
        }

        $dir->close();
        return true;
    }

    protected function recurseDelete($path): bool
    {
        if (is_dir($path)) {
            $objects = scandir($path);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir("{$path}/{$object}") && !is_link("{$path}/{$object}"))
                        $this->recurseDelete("{$path}/{$object}");
                    else
                        unlink("{$path}/{$object}");
                }
            }
            rmdir($path);
        } else {
            unlink($path);
        }
        return true;
    }

}