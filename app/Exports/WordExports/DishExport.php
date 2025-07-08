<?php

namespace App\Exports\WordExports;

use App\Models\Dish\Dish;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Writer\WriterInterface;

class DishExport {
    private Dish $_dish;

    public function __construct(Dish $dish) {
        $this->_dish = $dish;
    }


    public function getWriter() : WriterInterface {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();
        $tableStyle = array(
            'borderColor' => '006699',
            'borderSize'  => 6,
            'cellMargin'  => 50
        );
        
        $section->addText($this->_dish->name,array('name'=>'Arial','size' => 20,'bold' => true));
        
        $table = $section->addTable([$tableStyle]);
        $table->addRow();
        $table->addCell()->addText('№ п/п');
        $table->addCell()->addText('Наименование сырья');
        $table->addCell()->addText('Масса брутто, г, кг');
        $table->addCell()->addText('Масса нетто или полуфабриката, г, кг');
        $table->addCell()->addText('Масса готового продукта, г, кг');
        $table->addCell()->addText('Масса На 10 порций');
        
        return IOFactory::createWriter($phpWord, 'ODText');
    }
}