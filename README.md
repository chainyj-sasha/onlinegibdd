# Loyalty Service

Выполнил рефакторинг контроллера LoyaltyPointsController:
- Зарегистрировал сервис-провайдер [LoyaltyPointsServiceProvider.php](app%2FProviders%2FLoyaltyPointsServiceProvider.php)
- Создал сервис и интерфейс сервиса [LoyaltyPointsService.php](app%2FServices%2FLoyaltyPointsService.php) и [LoyaltyPointsServiceInterface.php](app%2FServices%2FLoyaltyPointsServiceInterface.php)
- Внедрил зависимость в корструкторе контроллера LoyaltyPointsController от интерфейса LoyaltyPointsServiceInterface
- Перенес бизнеслогику из контроллера в сервиc
- Создал валидацию входящих параметров в классах [CancelRequest.php](app%2FHttp%2FRequests%2FCancelRequest.php), [DepositRequest.php](app%2FHttp%2FRequests%2FDepositRequest.php), [WithdrawRequest.php](app%2FHttp%2FRequests%2FWithdrawRequest.php)
- Данные вытягиваются из $request (ранее было $_POST)
- Добавил типы возвращаемых данных в методах
- Задокументировал методы контроллера LoyaltyPointsController
- Рефакторинг каждого метода в отдельном коммите с подробным описанием изменений
