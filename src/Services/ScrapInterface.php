<?php

namespace App\Services;

interface ScrapInterface
{

    /**
     * Do the scrap action on sources.
     * @return mixed
     */
    public function scrapSources();

    /**
     * manipulate the data for publishing.
     * @return mixed
     */
    public function preparePost();

    /**
     * store the data in the database.
     * @return mixed
     */
    public function saveData();
}