<?php
	error_reporting(0);	
	$asin = $_GET['asin'];
	$hh=getAmazonPrice('com',$asin);
	echo "<pre>";
	print_r($hh);
	echo "</pre>";
	function aws_signed_request($region, $params)
{
	$public_key = "AKIAIHCKVPXGJM3ZGZOA";
	$private_key = "pwHOVM01k/QG0u/WGUJXdnSVh46WWptE7sqBngYA";
 
	$method = "GET";
	$host = "ecs.amazonaws." . "com";
	$host = "webservices.amazon." . "com";
	$uri = "/onca/xml";
 
	$params["Service"] = "AWSECommerceService";
	$params["AssociateTag"] = "009cd-20"; // Put your Affiliate Code here
	$params["AWSAccessKeyId"] = $public_key;
	$params["Timestamp"] = gmdate("Y-m-d\TH:i:s\Z");
	$params["Version"] = "2011-08-01";
   // print_r($params);
	ksort($params);
 
	$canonicalized_query = array();
	foreach ($params as $param => $value) 
	{
		$param = str_replace("%7E", "~", rawurlencode($param));
		$value = str_replace("%7E", "~", rawurlencode($value));
		$canonicalized_query[] = $param . "=" . $value;
	}
 
	$canonicalized_query = implode("&", $canonicalized_query);
 
	$string_to_sign = $method . "\n" . $host . "\n" . $uri . "\n" . $canonicalized_query;
	$signature = base64_encode(hash_hmac("sha256", $string_to_sign, $private_key, True));
	$signature = str_replace("%7E", "~", rawurlencode($signature));
 
	$request = "http://" . $host . $uri . "?" . $canonicalized_query . "&Signature=" . $signature;
	$response = getPage($request);
 
	//print_r($response);
 
	 $pxml = simplexml_load_string($response);
	if ($pxml === False) {
		echo 'na';
		return False;// no xml
	} else {
		return $pxml;
	}

}

function getPage($url)
{
 
	$curl = curl_init();
	curl_setopt ($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_FAILONERROR, true);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	
	$html = curl_exec($curl);
	echo curl_error($curl);
	curl_close($curl);
    //echo $html1= htmlentities($html);
	return $html;
}
	function getAmazonPrice($region, $asin) 
{ 
	$xml = aws_signed_request($region, array("Operation" => "ItemLookup",
											"ItemId" => $asin,
											//"SearchIndex " => "Books",
											//"IdType" => "ISBN",		
											"IncludeReviewsSummary" => false,
											//"SearchIndex"=>"Apparel",
											//"Condition"=>"New",
											//"Keywords"=>"Boys",
											"ResponseGroup" => "Large"
										));
 
	$item = $xml->Items->Item;
	
	$title = htmlentities((string) $item->ItemAttributes->Title);
	$manufacturer = htmlentities((string) $item->ItemAttributes->Manufacturer);
	$model = htmlentities((string) $item->ItemAttributes->Model);
	$brand = htmlentities((string) $item->ItemAttributes->Brand);
	$feature = htmlentities((string) $item->ItemAttributes->Feature);
	$listprice = htmlentities((string) $item->ItemAttributes->ListPrice->FormattedPrice);
	$upc = htmlentities((string) $item->ItemAttributes->UPC);
	$category = htmlentities((string) $item->ItemAttributes->ProductGroup);
	$color = htmlentities((string) $item->ItemAttributes->Color);
	$size = htmlentities((string) $item->ItemAttributes->Size);
	$itemdimension = $item->ItemAttributes->ItemDimensions;
	$height = htmlentities((string) $itemdimension->Height);
	$length  = htmlentities((string) $itemdimension->Length);
	$weight  = htmlentities((string) $itemdimension->Weight);
	$width   = htmlentities((string) $itemdimension->Width);
	$length=$length/100;
	$height=$height/100;
	$width=$width/100;
	if($weight){
		$weight =round($weight/100);
	$weight =$weight.' pounds';}
	if($height){
	$dimension=$length.'x'.$width.'x'.$height.' inches';}
	$url = htmlentities((string) $item->DetailPageURL);
	$image = htmlentities((string) $item->LargeImage->URL);
	$lowestprice = htmlentities((string) $item->OfferSummary->LowestNewPrice->FormattedPrice);
	$code = htmlentities((string) $item->OfferSummary->LowestNewPrice->CurrencyCode);
	$offers = htmlentities((string) $item->OfferSummary->TotalNew);
	$saleprice = htmlentities((string) $item->Offers->Offer->OfferListing->Price->FormattedPrice);
	$qty = htmlentities((string) $item->Offers->Offer->OfferListing->OfferListingId);
	$size = htmlentities((string) $item->ItemAttributes->Size);
	$acatname = (string) $item->BrowseNodes->BrowseNode->Ancestors->BrowseNode->Name;
	$catname = (string) $item->BrowseNodes->BrowseNode->Name;
	$lastcatname = (string) $item->BrowseNodes->BrowseNode->Children->BrowseNode->Name;
	$fullcategory=$category.'>'.$acatname.'>'.$catname;
	
	$content = $item->EditorialReviews->EditorialReview->Content;
	
		$response = array(
			//"code" => $code,
			"asin" => $asin,
			"upc" => $upc,
			"lowestprice" => $lowestprice,
			"image" => $image,
			"url" => $url,
			"category" => $fullcategory,
			"qty" => 2,
			"dimension" => $dimension,			
			"weight" => $weight,
			"size" => $size,
			"color" => $color,
			"brand" => $brand,
			"model" => $model,
			"manufacturer" => $manufacturer,
			"content" => $content,
			"feature" => $feature,
			"title" => $title,
			"listprice" => $listprice,
			"saleprice" => $saleprice,
			"acatname" => $acatname,
			"catname" => $catname,
			//"lastcatname" => $lastcatname
		);
		
 
	return $response;
	
}
	?>