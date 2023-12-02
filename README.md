# API Middleware

![image](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white) ![image](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white
) ![image](https://img.shields.io/badge/SQLite-07405E?style=for-the-badge&logo=sqlite&logoColor=white) ![image](https://img.shields.io/badge/Vite-B73BFE?style=for-the-badge&logo=vite&logoColor=FFD62Ehttps://img.shields.io/badge/SQLite-07405E?style=for-the-badge&logo=sqlite&logoColor=white)


This is helper app for fetching data from API. How it works? You have to send by POST params: 
required:
- "apiUrl": url string, complete address for API query (for example: "https://api.com/someparams")
- "apiName": your own name for your API query (string)
- "apiKey": security key, setted in .env file, needed for security verification (string)
optional:
- "apiAcceptedDataFreshness" (how old data you accept -> number of seconds). Default value is 10. (integer)

If validation of request will be succesfull, connection with 'apiUrl' will be succesfull, the response from API with your 'apiName' will be stored in database and returned to you. If you will want to grab data from this API again (whole URL needs to be the same), app will check if query form this api exist in DB. If it exist - will compare it freshness with "apiAcceptedDataFreshness" param. If response from this API is exist in DB and is fresh - will return it directly from database, if exist but is not fresh, will be fetch again from API source and then return.

CORS headers are reset.



## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).




 	



