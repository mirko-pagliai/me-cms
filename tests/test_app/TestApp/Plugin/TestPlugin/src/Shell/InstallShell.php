<?php
declare(strict_types=1);

namespace TestPlugin\Shell;

use Cake\Console\Shell;

class InstallShell extends Shell
{
    public function all(): bool
    {
        return true;
    }
}
