<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title><?= $this->pageTitle ? $this->pageTitle  . ' &ndash; ' . SITE_NAME : SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <link href="<?= $this->getStylesheet("styles"); ?>" rel="stylesheet" id="main-css">
    <link href="<?= $this->getStylesheet("reset"); ?>" rel="stylesheet">
    <!-- <link href="<?= $this->getImage("favicon.ico"); ?>" rel="icon"> -->
</head>
<body>
    <main id="root">
        <section id="main-content">