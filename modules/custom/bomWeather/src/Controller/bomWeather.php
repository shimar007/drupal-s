<?php 
//import weather data using BOM weather
namespace Drupal\bomWeather\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

class bomWeather extends ControllerBase { 
    
    public function importData() {
        if(!$data = file_get_contents("ftp://ftp2.bom.gov.au/anon/gen/fwo/IDN11060.xml")) {
            echo "Could not fetch weather data\n";
            exit();
        }
        //convert xml to object
        $xml = simplexml_load_string($data);
        
        //NSW_PT047 - Dubbo Data
        //NSW_PT131 - Sydney Data
        foreach($xml->forecast->area as $area) {
            if($area->attributes()->aac == 'NSW_PT047' || $area->attributes()->aac == 'NSW_PT131') {
                foreach($area->{'forecast-period'} as $forecastPeriod) {
                    $city_forecast_text = $forecastPeriod->text[0];
                    $city_forecast_code = $forecastPeriod->element[0];
                }    
            }
        }

        return array('#markup' => $my_module_template);
    }
}
?>