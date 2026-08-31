<!DOCTYPE html>
<html lang="fr">
<head><meta charset="utf-8"></head>
<body style="font-family: Arial, sans-serif; font-size: 15px; color: #1f2421; line-height: 1.5;">
<div>{!! $corpsHtml !!}</div>
<p><img src="{{ $message->embed($cheminPng) }}" alt="Carte d'adhérent" style="max-width: 600px; width: 100%; height: auto;"></p>
</body>
</html>
