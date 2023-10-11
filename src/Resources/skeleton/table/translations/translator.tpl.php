# src/Acme/DemoBundle/Resources/translations/messages.fr.yml
<?= $entity_class_name_lc ?>:
    home:
        title: <?= $entity_class_name_up . PHP_EOL?>
        sub-title: Interface
    setup-tool:
        title: Menu
    form:
        ADD-YOUR-FIELDS.label: <?= $entity_class_name_up . " Controller->index->filterFields([ICI])" .PHP_EOL?>
        add.label: Ajouter
        update.label: Modifier
        delete.label: Supprimer
        delete.msg.label : Êtes-vous sûr de bien vouloir supprimer cet élément ?
        pin.publish.1 : Êtes-vous sûr de bien vouloir de-publier cet élément ?
        pin.publish.0 : Êtes-vous sûr de bien vouloir publier cet élément ?
        save.label: Enregistrer
        home:
            title: <?= $entity_class_name_up ?> Manager
            title-edit: Edit <?= $entity_class_name_up . PHP_EOL?>
            title-new: New <?= $entity_class_name_up . PHP_EOL?>
        name:
            label: Nom
            placeholder: Saisir nom un pour le tags ...
<?php foreach ($entity_fields as $field): ?>
        <?= lcfirst($field['fieldName']) ?>:
            label: <?= lcfirst($field['fieldName']) . PHP_EOL ?>
            placeholder: Saisir nom un pour le tags ...
<?php endforeach; ?>