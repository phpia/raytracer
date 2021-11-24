<?php
// phpz main.php

$start = microtime(true);


require_once "Vec3.php";
require_once "Ray.php";
require_once "Hittable.php";
require_once "Sphere.php";
require_once "Camera.php";
require_once "Camera_DOF.php";
require_once "Material.php";

main();

fwrite(STDERR, sprintf("Total time: %s\r\nMemory Used (current): %s\r\nMemory Used (max): %s", round(microtime(true) - $start, 4), formatBytes(memory_get_usage()), formatBytes(memory_get_peak_usage())));

function main(){

    // Image
    $aspect_ratio = 3 / 2;
    $image_width = 200;
    $image_height = intval($image_width / $aspect_ratio);
    $samples_per_pixel = 100;//100;
    $max_depth = 50;//50


    // World        
    $world = random_scene();   
    
    // Camera    
    //$cam = new Camera([-2,2,1],[0,0,-1],[0,1,0],90,$aspect_ratio);
    $lookfrom = [13,2,3];
    $lookat = [0,0,0];
    $vup = [0,1,0];
    $dist_to_focus = 10;//Vec3::length(Vec3::op('-',$lookfrom,$lookat));
    $aperture = 0.1;
    
    $cam = new Camera_DOF($lookfrom, $lookat, $vup, 20, $aspect_ratio, $aperture, $dist_to_focus);

    //$cam = new Camera([-2,2,1], [0,0,-1], [0,1,0], 90, $aspect_ratio);


    
    // Render
    $str = "P3\n" . $image_width . ' ' . $image_height . "\n255\n";
    $operaciones = 0;
    for ($j = $image_height-1; $j >= 0; --$j) {
        fwrite(STDERR,  "\rScanlines remaining: " . $j . ' ' );
        for ($i = 0; $i < $image_width; ++$i) {
            
            $pixel_color = [0,0,0];
            for ($s = 0; $s < $samples_per_pixel; ++$s) {
                $u = ($i + random_double()) / ($image_width-1);
                $v = ($j + random_double()) / ($image_height-1);
                $r = $cam->get_ray($u, $v);
                //$pixel_color = $pixel_color->op('+',ray_color($r, $world));
                $pixel_color = Vec3::op ('+',$pixel_color,ray_color($r, $world, $max_depth));
                $operaciones++;
            }

            $str .= Vec3::write_color($pixel_color, $samples_per_pixel);            
        }
    }

    echo $str;
    fwrite(STDERR,  "\nDone $operaciones\n" );
}

function ray_color(Ray $r, $world, $depth) {
    // If we've exceeded the ray bounce limit, no more light is gathered.
    if ($depth <= 0)
        return [0,0,0];

    $rec = false;
    foreach ($world as $object){
        $new_rec = $object->hit($r, 0.001, PHP_FLOAT_MAX);
        if ($new_rec){
            if (!$rec) {
                $rec = $new_rec;
            }
            else {
             if ($new_rec->t < $rec->t) 
                $rec = $new_rec;
            }
        }
        

    }

    if ($rec) {            
        if ($rec->material->scatter($r, $rec))
            return Vec3::op('*',$rec->material->get_color_attenuation(), ray_color($rec->material->get_scattered(), $world, $depth-1));
        return [0,0,0];

        //$c = Vec3::op('+', $rec->normal, [1,1,1]);
        //$c = Vec3::escalar_op('*',$c,0.5);
        //return [$c[0], $c[1], $c[2]];

        //$target = Vec3::op('+',$rec->p, Vec3::op('+',$rec->normal,random_in_unit_sphere()));
        //$target = Vec3::op('+',$rec->p, Vec3::op('+',$rec->normal,random_unit_vector()));
            /*
        $target = Vec3::op('+',$rec->p , random_in_hemisphere($rec->normal));


        $c = ray_color(new Ray($rec->p, Vec3::op('-',$target,$rec->p)), $world, $depth-1);
        $c = Vec3::escalar_op('*',$c,0.5);
        return $c;*/
    }
   
    $unit_direction = Vec3::unit_vector($r->direction());
    $t = 0.5*($unit_direction[1] + 1.0);
    $color1 = [1,1,1];
    $color1 = Vec3::escalar_op('*',$color1,1-$t);
    $color2 = [0.5, 0.7, 1.0];
    $color2 = Vec3::escalar_op('*',$color2,$t);
  

    $c = Vec3::op('+',$color1, $color2);
    return [$c[0], $c[1], $c[2]];
}



function random_scene() {
    $world = [];
     
    $material3 = new Metal([0.7, 0.6, 0.5], 0.0);
    $world[] = new Sphere([4,1,0], 1, $material3);     
    
    $material1 = new Dielectric(1.5);
    $world[] = new Sphere([0,1,0], 1, $material1); 

    $material2 = new Lambertian([0.4, 0.2, 0.1]);    
    $world[] = new Sphere([-4,1,0], 1, $material2);   

    for ($a = -11; $a < 11; $a++) {
        for ($b = -11; $b < 11; $b++) {
            $choose_mat = random_double();
            $center = [$a + 0.9*random_double(), 0.2, $b + 0.9*random_double()];
            
            if (Vec3::length(Vec3::op('-',$center,[4,0.2,0])) > 0.9) {
                if ($choose_mat < 0.8) {
                    // diffuse
                    $albedo = Vec3::op('*', Vec3::random(), Vec3::random()); 
                    $sphere_material = new Lambertian($albedo);                    
                } else if ($choose_mat < 0.95) {
                    // metal
                    $albedo = Vec3::random(0.5, 1);
                    $fuzz = random_double(0, 0.5);
                    $sphere_material = new Metal($albedo, $fuzz);                    
                } else {
                    // glass
                    $sphere_material = new Dielectric(1.5);                    
                }
                $world[] = new Sphere($center, 0.2, $sphere_material);
            }
        }
    }

       

    $ground_material = new Lambertian([0.5, 0.5, 0.5]);
    $world[] = new Sphere([0, -1000, -0], 1000, $ground_material);


    return $world;
}



function clamp($x, $min, $max) {
    if ($x < $min) return $min;
    if ($x > $max) return $max;
    return $x;
}

function random_double() {
    // Returns a random real in [0,1).
    return rand() / (getrandmax() + 1.0);
}

function random_double_r($min, $max) {
    // Returns a random real in [min,max).
    return $min + ($max-$min) * random_double();
}

function random_in_unit_sphere() {
    while (true) {
        $p = Vec3::random(-1,1);
        if (Vec3::length_squared($p) >= 1) continue;
        return $p;
    }
}

function random_in_unit_disk(): array {
    while (true) {
        $p = [random_double(-1,1), random_double(-1,1), 0];
        if (Vec3::length_squared($p) >= 1) continue;
        return $p;
    }
}

function random_unit_vector() {
    return Vec3::unit_vector(random_in_unit_sphere());
}

function random_in_hemisphere($normal) {
    $in_unit_sphere = random_in_unit_sphere();
    if (Vec3::dot($in_unit_sphere, $normal) > 0.0) // In the same hemisphere as the normal
        return $in_unit_sphere;
    else
        return Vec3::negative($in_unit_sphere);
}

function formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 
   
    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 
   
    $bytes /= pow(1024, $pow); 
   
    return round($bytes, $precision) . ' ' . $units[$pow]; 
}
