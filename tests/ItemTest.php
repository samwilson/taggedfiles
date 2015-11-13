<?php

class ItemTest extends \PHPUnit_Framework_TestCase {

    /**
     * @testdox An Item has an ID and title.
     */
    public function meta() {
        $item = new App\Item();
        $item->save();
        $this->assertEquals(1, $item->getId());
    }

}
