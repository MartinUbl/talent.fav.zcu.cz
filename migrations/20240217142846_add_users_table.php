<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddUsersTable extends AbstractMigration
{
    public function change(): void
    {
        $tbl = $this->table("users");

        $tbl->addColumn('fullname', 'string', ['limit'=> 255])
            ->addColumn('password','string', ['null' => true, 'limit'=> 255])
            ->addColumn('email', 'string', ['null' => false, 'limit'=> 255])
            ->addColumn('google_id', 'string', ['null' => true]);

        $tbl->create();
    }
}
