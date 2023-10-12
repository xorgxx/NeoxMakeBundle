{% extends 'admin/_layout-administrator.html.twig' %}

{% block title %}{{ mode }} <?= $entity_class_name ?>{% endblock %}

{% block contentSub %}
<turbo-frame id="neoxTable">
    <div class="mt-5 mb-5">
        <div class="d-flex justify-content-between">
            <h1>{{ mode }} <?= $entity_class_name ?></h1>
            <a style="height: 45px" class="button button-info button-rounded" href="{{ path('<?= $route_name ?>_index') }}">Annuler</a>
        </div>

        {{ include('<?= $templates_path ?>/_form.html.twig') }}

    </div>
</turbo-frame>
{% endblock contentSub %}