<?php


Form::macro('field', function ($field) {
    $form = Rapyd::getForm();
    if ($form) return $form->field($field);
});
