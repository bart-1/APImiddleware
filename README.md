# API Middleware

![image](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white) ![image](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white
) ![image](https://img.shields.io/badge/SQLite-07405E?style=for-the-badge&logo=sqlite&logoColor=white) ![image](https://img.shields.io/badge/Vite-B73BFE?style=for-the-badge&logo=vite&logoColor=FFD62Ehttps://img.shields.io/badge/SQLite-07405E?style=for-the-badge&logo=sqlite&logoColor=white)


This is helper app for fetching data from API. How it works? You have to send by POST params: "apiUrl" (for example: "https://api.com/someparams"), "apiName" (your own name for your API query), "apiKey" - security key, setted in .env file, needed for security verification and "apiExpired (how old data you accept -> numer of seconds). If connection with 'apiUrl' will be succesfull, response from API and your 'apiName' will be stored in database and API response will be returned to you. If you will want to grab data from this API again, app will check if query form this api exist in DB. If it exist - will compare it freshness with "apiExpirationTime" param. If response from this API is exist in DB and is fresh - will return it directly from database, if exist but is not fresh, will fetch it again from API source and then return.

CORS headers are reset.

<!-- Freshness you can set as seconds in second param in compareTimestampToNow function in APIController:

```php

 public function verifyLocalData($localData): void
 {
   // second param in compareTimestampToNow is number of seconds
   $verifyTime = $this->compareTimestampToNow($localData->value('updated_at'), 10);

   if ($verifyTime) {
       $this->apiResponseData = $localData->value('api-response');
   } else {
      $this->fetchAPI($this->url);
      $id = $localData->value('id');
      $this->repository->update($id, ["api-response" => $this->apiResponseData, "api-name" => $this->apiName]);
  }
 }
``` -->

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).




 	



