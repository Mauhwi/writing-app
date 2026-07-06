A little writing app for two

Dev:
<ul>
<li>http://localhost:8000/</li>
<li>docker compose up -d</li>
<li>php -S 0.0.0.0:8000 -t public</li>
<li>npm run dev</li>
<li>php artisan reverb:start</li>
<li>php artisan queue:work</li>
</ul>


Server:
<ul>
<li>git pull</li>
<li>composer install --no-dev</li>
<li>npm run build</li>
<li>php artisan migrate --force</li>
<li>php artisan config:cache</li>
<li>php artisan route:cache</li>
<li>php artisan view:cache</li>
<li>sudo supervisorctl restart all</li>
</ul>
