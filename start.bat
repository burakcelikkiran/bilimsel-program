
@echo off



echo  config temizleniyor..
php artisan config:clear
echo config temizlendi.

echo Cache temizleniyor...
php artisan cache:clear
echo Cache temizlendi.


echo route temizleniyor...
php artisan route:clear
echo route temizlendi.

echo View cache temizleniyor...
php artisan view:clear
echo View cache temizlendi.


echo route:cache temizleniyor...
php artisan route:cache
echo route:cache temizlendi.

echo Optimize cache temizleniyor...
php artisan optimize:clear
echo Optimize cache temizlendi.

echo Laravel sunucusu başlatılıyor...
php artisan serve

pause

