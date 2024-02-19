<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddGrabTheLabProjectTable extends AbstractMigration
{
    public function change(): void
    {
        $tbl = $this->table("grabthelab_project");

        $tbl->addColumn("users_id", "integer", ["null" => false, "signed" => false])
            ->addColumn("data", "text");

        $tbl->addForeignKey("users_id", "users", "id");

        $tbl->create();
    }
}
