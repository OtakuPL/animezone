<?php $view->extend('content') ?>

<?php $view['slots']->set('_sidebar', $view->render('Sidebar/episodes/layout', array('current' => 'servers'))) ?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><i class="glyphicon glyphicon-remove-sign"></i> Usuń serwer</h3>
        <a href="<?= $app->generateUrl('types_index') ?>" class="btn btn-xs btn-danger pull-right btn-helper"><i class="fa fa-reply"></i>Wróć do listy</a>
    </div>
    <div class="panel-body">
        <div class="bs-callout bs-callout-info">
            <p>Jesteś pewien że chcesz usunać serwer: <strong><?= $server->getName() ?></strong>, w raz z nim wszystkie powiązania?</p>
        </div>
        <form class="form-horizontal" role="form" method="post" action="<?= $app->generateUrl('servers_delete', array('serverID' => $server->getId())) ?>">
            <div class="text-center">
                <input type="hidden" name="id" value="<?= $server->getId() ?>">
                <button type="submit" class="btn btn-success"><i class="fa fa-thumbs-o-up"></i> Usuń</button>
                <a href="<?= $app->generateUrl('servers_index') ?>" class="btn btn-danger"><i class="fa fa-thumbs-o-down"></i> Anuluj</a>
            </div>
        </form>
    </div>
</div>