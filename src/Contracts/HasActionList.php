<?php

namespace Laratusk\Larasvg\Contracts;

interface HasActionList
{
    /**
     * Get the full list of available Inkscape actions.
     */
    public function actionList(): string;
}
