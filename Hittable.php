<?php

class Hit_record {
    public $p;
    public $normal;
    public $t;
    public $front_face; 
    public $material;   

    public function __construct(array $p = NULL, array $normal = NULL, float $t = NULL) {
        $this->p = $p;
        $this->normal = $normal;
        $this->t = $t;
    }

    public function set_face_normal(Ray $r, array $outward_normal) {
        $this->front_face = Vec3::dot($r->direction(), $outward_normal) < 0;
        $this->normal = $this->front_face ? $outward_normal : Vec3::negative($outward_normal); 
    }
};

abstract class Hittable
{
    // Force Extending class to define this method
    abstract protected function hit(Ray $r, float $t_min, float $t_max);
    
    // Common method
    public function printOut() {
        print $this->getValue() . "\n";
    }
}
/*
class  Hittable_list extends Hittable {
    public $hittable_list;

    public function hit(Ray $r, float $t_min, float $t_max) {
        //hit_record temp_rec;
        $hit_anything = false;
        $closest_so_far = $t_max;
    
        //for (const auto& object : objects) {
        foreach ($this->hittable_list as $object){
            if ($temp_rec = $object->hit($r, $t_min, $closest_so_far)) {
                $hit_anything = true;
                $closest_so_far = $temp_rec->t;
                $rec = $temp_rec;
            }
        }
    
        return hit_anything;
    }
}
*/
