<?= "<?php\n" ?>

namespace <?= $namespace ?>;

<?= $use_statements; ?>
// use App\Controller\Core;

#[Route('/%admin%<?= $route_path ?>')]
class <?= $class_name ?> extends AbstractController
{
use Core;
<?= $generator->generateRouteForControllerMethod('/', sprintf('%s_index', $route_name), ['GET']) ?>
<?php if (isset($repository_full_class_name)): ?>
    public function index(Request $request, <?= $repository_class_name ?> $<?= $repository_var ?>): Response
    {
    $neoxTable = $this->neoxTableBuilder
    ->filterFields("#,'ADD-YOUR-FIELDS'", "<?= $entity_twig_var_singular ?>")
    ->setEntity($<?= $repository_var ?>->findAll())
    ->setActButton("@<?= $route_name ?>")
    ;

    // 🔥 The magic happens here! 🔥
    if ( $this->neoxTableBuilder::checkTurbo($request) ) {
    return $this->render('@NeoxMake/neoxTable.html.twig',["neoxTable" => $neoxTable  ]);
    }

    return $this->render('<?= $templates_path ?>/index.html.twig', [
    'neoxTable' => $neoxTable,
    ]);
    }
<?php else: ?>
    public function index(EntityManagerInterface $entityManager): Response
    {
    $<?= $entity_var_plural ?> = $entityManager
    ->getRepository(<?= $entity_class_name ?>::class)
    ->findAll();

    return $this->render('<?= $templates_path ?>/index.html.twig', [
    '<?= $entity_twig_var_plural ?>' => $<?= $entity_var_plural ?>,
    ]);
    }
<?php endif ?>

<?= $generator->generateRouteForControllerMethod('/new', sprintf('%s_new', $route_name), ['GET', 'POST']) ?>

public function new(Request $request, <?= $repository_class_name ?> $<?= $repository_var ?>): Response

{
$<?= $entity_var_singular ?> = new <?= $entity_class_name ?>();
$form = $this->createForm(<?= $form_class_name ?>::class, $<?= $entity_var_singular ?>, [
'action' => $this->generateUrl('<?= sprintf('%s_new', $route_name) ?>', []),
'method' => 'POST',
]);

$form->handleRequest($request);

if ($form->isSubmitted() && $form->isValid()) {
$<?= $repository_var ?>->save($<?= $entity_var_singular ?>, true);
$this->addFlash('success', "Enregistrement a été ajouté.");
// 🔥 The magic happens here! 🔥
if ( $this->neoxTableBuilder::checkTurbo($request) ) {
return $this->render('@NeoxMake/msg.stream.html.twig', ["domaine" => "<?= $entity_twig_var_singular ?>"]);
}
return $this->redirectToRoute('<?= $route_name ?>_index', [], Response::HTTP_SEE_OTHER);
}

<?php if ($use_render_form) { ?>
    return $this->render('<?= $templates_path ?>/crud.html.twig', [
    '<?= $entity_twig_var_singular ?>' => $<?= $entity_var_singular ?>,
    'mode'  => $this->getTranslator()->trans("<?= $entity_var_singular ?>.form.add.label",[],"<?= $entity_var_singular ?>"),
    'form'  => $form,
    ]);
<?php } else { ?>
    return $this->render('<?= $templates_path ?>/crud.html.twig', [
    '<?= $entity_twig_var_singular ?>' => $<?= $entity_var_singular ?>,
    'mode'  => $this->getTranslator()->trans("<?= $entity_var_singular ?>.form.add.label",[],"<?= $entity_var_singular ?>"),
    'form'  => $form,
    ]);
<?php } ?>
}

<?= $generator->generateRouteForControllerMethod(sprintf('/{%s}', $entity_identifier), sprintf('%s_show', $route_name), ['GET']) ?>
public function show(<?= $entity_class_name ?> $<?= $entity_var_singular ?>): Response
{
return $this->render('<?= $templates_path ?>/show.html.twig', [
'<?= $entity_twig_var_singular ?>' => $<?= $entity_var_singular ?>,
]);
}

<?= $generator->generateRouteForControllerMethod(sprintf('/{%s}/edit', $entity_identifier), sprintf('%s_edit', $route_name), ['GET', 'POST']) ?>

public function edit(Request $request, <?= $entity_class_name ?> $<?= $entity_var_singular ?>, <?= $repository_class_name ?> $<?= $repository_var ?>): Response

{
$form = $this->createForm(<?= $form_class_name ?>::class, $<?= $entity_var_singular ?>, [
'action' => $this->generateUrl('<?= sprintf('%s_edit', $route_name) ?>', ["id"=> $<?= $entity_var_singular ?>->getId()]),
'method' => 'POST',
]);
$form->handleRequest($request);

if ($form->isSubmitted() && $form->isValid()) {
$<?= $repository_var ?>->save($<?= $entity_var_singular ?>, true);
$this->addFlash('success', "Enregistrement a été modifier.");

// 🔥 The magic happens here! 🔥
if ( $this->neoxTableBuilder::checkTurbo($request) ) {
return $this->render('@NeoxMake/msg.stream.html.twig', ["domaine" => "<?= $entity_twig_var_singular ?>"]);
}
return $this->redirectToRoute('<?= $route_name ?>_index', [], Response::HTTP_SEE_OTHER);
}

<?php if ($use_render_form) { ?>
    return $this->render('<?= $templates_path ?>/crud.html.twig', [
    '<?= $entity_twig_var_singular ?>' => $<?= $entity_var_singular ?>,
    'mode'  => $this->getTranslator()->trans("<?= $entity_var_singular ?>.form.update.label",[],"<?= $entity_var_singular ?>"),
    'form' => $form,
    ]);
<?php } else { ?>
    return $this->render('<?= $templates_path ?>/crud.html.twig', [
    '<?= $entity_twig_var_singular ?>' => $<?= $entity_var_singular ?>,
    'mode'  => $this->getTranslator()->trans("<?= $entity_var_singular ?>.form.update.label",[],"<?= $entity_var_singular ?>"),
    'form' => $form,
    ]);
<?php } ?>
}


<?= $generator->generateRouteForControllerMethod(sprintf('/{%s}/pin', $entity_identifier), sprintf('%s_pin', $route_name), ['POST']) ?>
public function pin(Request $request, <?= $entity_class_name ?> $<?= $entity_var_singular ?>, <?= $repository_class_name ?> $<?= $repository_var ?>): Response

{
if ($this->isCsrfTokenValid('pin'.$<?= $entity_var_singular ?>->get<?= ucfirst($entity_identifier) ?>(), $request->request->get('_token'))) {
$onOff = !$<?= $entity_var_singular ?>->isPublish();
$<?= $entity_var_singular ?>->setPublish($onOff);
$<?= $repository_var ?>->save($<?= $entity_var_singular ?>, true);
$msg = $onOff ? "Post est visible de tous." : "Post n'est plus visible de tous.";
$this->addFlash('success', $msg);

// 🔥 The magic happens here! 🔥
if ( $this->neoxTableBuilder::checkTurbo($request) ) {
return $this->render('@NeoxMake/msg.stream.html.twig', ["domaine" => "<?= $entity_var_singular ?>"]);
}
}

return $this->redirectToRoute('<?= $route_name ?>_index', [], Response::HTTP_SEE_OTHER);
}


<?= $generator->generateRouteForControllerMethod(sprintf('/{%s}', $entity_identifier), sprintf('%s_delete', $route_name), ['POST']) ?>
public function delete(Request $request, <?= $entity_class_name ?> $<?= $entity_var_singular ?>, <?= $repository_class_name ?> $<?= $repository_var ?>): Response

{
if ($this->isCsrfTokenValid('delete'.$<?= $entity_var_singular ?>->get<?= ucfirst($entity_identifier) ?>(), $request->request->get('_token'))) {
$<?= $repository_var ?>->remove($<?= $entity_var_singular ?>, true);
$this->addFlash('success', "Enregistrement a été supprimé.");

// 🔥 The magic happens here! 🔥
if ( $this->neoxTableBuilder::checkTurbo($request) ) {
return $this->render('@NeoxMake/msg.stream.html.twig', ["domaine" => "<?= $entity_var_singular ?>"]);
}
}


return $this->redirectToRoute('<?= $route_name ?>_index', [], Response::HTTP_SEE_OTHER);
}
}