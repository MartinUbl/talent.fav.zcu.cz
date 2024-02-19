<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddGrabTheLabProposalsTable extends AbstractMigration
{
    public function change(): void
    {
        $tbl = $this->table('grabthelab_proposals', ['id' => false, 'primary_key' => ['grabthelab_project_id', 'grabthelab_rounds_id']]);

        $tbl->addColumn('grabthelab_project_id', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('grabthelab_rounds_id', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('proposed_at', 'datetime');

        $tbl->addForeignKey('grabthelab_project_id', 'grabthelab_project', 'id')
            ->addForeignKey('grabthelab_rounds_id', 'grabthelab_rounds', 'id');

        $tbl->create();
    }
}
