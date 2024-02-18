<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUserRole extends AbstractMigration
{
    public function change(): void
    {
        $this->table('users')->addColumn('role', 'string', [ 'limit' => 32, 'default' => 'user', 'null' => false ])->update();
    }
}
