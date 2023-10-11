{% extends 'admin/_layout-administrator.html.twig' %}

{% block title %}{{ "#{neoxTable.domaine}.home.title"|default('Nc...')|trans({}, neoxTable.domaine)|capitalize }} index{% endblock %}

{% block stylesheets %}
    {{ parent() }}
    <link rel="stylesheet" href="{{ asset('build/canvas/css/components/bs-switches.css') }}"/>
    <link rel="stylesheet" href="{{ asset('build/canvas/css/components/radio-checkbox.css') }}"/>


{% endblock %}

{% block contentSub %}
    {# ------- Render -------- #}
     {% include'@NeoxTable/neoxTable.html.twig' with  {'neoxTable': neoxTable } %}
    {# ======== End Block ========= #}
{% endblock %}

{% block javascripts %}
    {{ parent() }}
    <script type="text/javascript" src="{{ asset('build/canvas/js/components/bs-switches.js') }}"></script>
    {#    <script type="text/javascript" src="{{ asset('build/canvas/js/components/bs-datatable.js') }}"></script>#}
    <script>
        $(".bt-switch").bootstrapSwitch();
    </script>
{% endblock javascripts %}