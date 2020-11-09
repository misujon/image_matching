<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Goutte\Client;

class Search extends Controller
{
    public function __construct()
    {
        /**
         * Defined the maximum time limit of the action that runs on this controller.
         * Sometimes that can be more lengthy for multiple website searching to match the image.
        */
        set_time_limit(10000);
    }


    /**
     * @findString function works to check the multiple values with a string.
     * @Params:2 required - 1. Array, 2. String
     * Array contains multiple words that matches to the string and if matches one,
     * The function returns TRUE, and if not match any then throw FALSE.
    */
    public function findString($array, $string) {
        foreach ( $array as $a ) {
            /**
             * Checking if the string has any array value matched or not.
             */
            if ( strpos ( $string , $a ) !== FALSE ){
                return true;
            }
        }
        return false;
    }


    /**
     * @url_exists function checks a url that by sending http request on the url, to check if the url is valid or not.
     * @Params Only one parameter is required called URL.
     * The url requested by the function and get the response with headers. By trimming and validating the response,
     * That can be defined if the request returns response code 200 or 302 or others.
     * If returns 200 or 302 that means the url is responding in valid way.
    */
    public function url_exists($url) {
        $timeout = 20;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

        $http_response = curl_exec($ch);
        $http_response = trim(strip_tags($http_response));
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        /**
         * Checking if the request is responding valid or not.
        */
        if (($http_code == "200") || ($http_code == "302")){
            return true;
        }else{
            return false;
        }

        curl_close($ch);
    }

    /**
     * @getSearchResult function perform it's action while a user submitted image and required information to match images.
     * The function only runs with post request that defines on laravel web route.
     * The function checks if all the data is available or not. and then performs next actions. If not all the data found
     * that returns to previous page with a message.
     * @Libraries,
     *      @Validator - Works for validating input data by laravel,
     *      @Goutte - Works to scrap a website data by it's url. An external library.
     *                Ref: https://goutte.readthedocs.io/en/latest/
     *
     *
    */
    public function getSearchResult(Request $request){

        /** Validating if the requested data is exists or not. **/
        $validate = Validator::make($request->all() ,[
            'image' => 'required',
            'websites' => 'required',
            'tags' => 'required'
        ]);

        if ($validate->fails()){
            return redirect()->back()->with('message', $validate->errors()->first());
        }else{
            $file = $request->file('image');                            /** Getting Image input from user */
            $encoded = base64_encode(file_get_contents($file->path()));     /** Encoded image by base64 hashing algorithm */
            $same = [];
            $related = [];
            $total_url = [];
            $Invalid = [];
            foreach ($request->input('websites') as $urls){
                $url_image = $urls['urls'];

                /**
                 * Creating object of the @Goutte scrapping library to pull data from the websites,
                 * that inputs by user.
                 */
                $client = new Client();
                $crawler = $client->request('GET', $url_image);         /** Data pulling or fetching by GET request method and*/
                $get_url_host = parse_url($url_image, PHP_URL_HOST);

                /**
                 * Filtering fetched data by RegEx regular expression
                 * That matches all the image tags from the scraped data and return as an array data
                 */
                preg_match_all("/(<img[^>]+>(?:<\/img>)?)/i", $crawler->html(), $matches);

                foreach ($matches[0] as $image){
                    preg_match('@src="([^"]+)@', $image, $match);       /** Filtering each image and get it's actual url */
                    preg_match('@alt="([^"]+)@', $image, $matchAlt);    /** Getting Alt information by filtering each image */

                    $actual = (isset($match[1]))?$match[1]:array_pop($match);

                    /**
                     *  - Checking if the url structure is valid or not and validating by the function @findString.
                     *  - Each grabbed image checks that is matching with the uploaded image by hashing them by base64 encoding.
                     *  - If the image matched then it's assigned to $same array variable and if not matched,
                     *    Then checks if the image alt string is matching with the inputted tags or not.
                     *  - If the tags matches with alt string that goes to $related array variable.
                     */
                    if (filter_var($actual, FILTER_VALIDATE_URL)){

                        /** Encoding image by base64_encode function and checking validity by url_exists function. */
                        if ($this->url_exists($match[1]) == true && base64_encode(file_get_contents($actual)) == $encoded){
                            $same[] = array('website'=> $get_url_host, 'url'=>$actual, 'alt'=>((isset($matchAlt[1]))?$matchAlt[1]:array_pop($matchAlt)));
                        }else{
                            $tags = explode(',', $request->input('tags'));

                            /** Matching tags with the image alt information. */
                            if ($this->findString($tags, ((isset($matchAlt[1]))?$matchAlt[1]:array_pop($matchAlt)))){
                                $related[] = array('website'=> $get_url_host, 'url'=>$actual, 'alt'=>((isset($matchAlt[1]))?$matchAlt[1]:array_pop($matchAlt)));
                            }
                        }
                    }else{
                        $Invalid[] = 1;     /** Getting all the invalid urls */
                    }
                    $total_url[] = 1;       /** Getting all the urls searched */
                }
            }

            /** Data formatting to send in view. */
            $data = array(
                'matched' => $same,
                'related' => $related,
                'websites' => $request->input('websites'),
                'tags'     => explode(',', $request->input('tags')),
                'search_image' => $encoded,
                'search_image_mime' => $file->getMimeType(),
                'url_finds' => count($total_url),
                'invalids' => count($Invalid)
            );

            return view('search')->with('data', $data);

        }
    }
}
