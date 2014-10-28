<?php
namespace AnnotationTestResource;

class Momonga {
    /**
     * @Inject
     * @Named("p1=prop")
     */
    public $p1;

    /**
     * @Inject
     * @Named('p1=param')
     * @Named('p2=param,p3=param')
     */
    public function __construct($p)
    {
    }
}
