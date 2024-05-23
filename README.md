## MiniShop3

Компонент для построения интернет-магазина на базе MODX3

### Системные требования

- MODX версии 3.0.0 и выше
- PHP 8.1 и выше

### Установка

1. Проверить, присутствует ли `composer.json` в корне сайта. Если нет - установить:
```
cd /to/modx/root/
wget https://raw.githubusercontent.com/modxcms/revolution/v3.0.5-pl/composer.json
```

2. Добавить **временные** репозитории для дополнений:
```
composer config repositories.pdotools vcs https://github.com/bezumkin/pdoTools
composer config repositories.minishop3 vcs https://github.com/bezumkin/MiniShop3
```

3. (опционально) Если вы на modhost.pro, то подготовить консольный PHP:
```
mkdir ~/bin
ln -s /usr/bin/php8.1 ~/bin/php
source ~/.profile
```

4. Установить дополнение
``` 
composer require modx-pro/minishop3
```

5. Запустить установку компонента в MODX
```
composer exec minishop3 install  
```

Обязательное дополнение `modx-pro/pdotools` будет скачано и установлено автоматически.

### Удаление

Для удаления дополнения нужно выполнить команды в обратном порядке:
```
composer exec minishop3 remove
composer remove modx-pro/minishop3
```

Если вам больше не нужен pdoTools - можно удалить и его:
```
composer exec pdotools remove
composer remove modx-pro/pdotools
```
