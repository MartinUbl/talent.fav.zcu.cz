<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddGrabTheLabProjectState extends AbstractMigration
{
    public function change(): void
    {
        $this->table("grabthelab_project")
            ->addColumn('state', 'string', [ 'limit' => 32 ])
            ->update();
    }
}
