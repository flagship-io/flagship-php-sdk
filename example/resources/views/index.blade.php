<!DOCTYPE>
<html lang="en">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8" />
    <script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script>
    <script src="https://flagship-qa-front.netlify.app/lib/qa.umd.js"></script>
    <link href="https://flagship-qa-front.netlify.app/lib/qa.css" rel="stylesheet" />
    <title>QA PHP</title>
</head>
<body>
<div id="app">
    <qa
        :technology="technology"
        :branch="branch"
        :features="features"
        :environment="environment"
    ></qa>
</div>
<script>
    const app = new Vue({
        el: "#app",
        data: {
            technology: "PHP",
            branch: "master",
            environment: "prod",
            features: FSFeatures.All,
        },
    });
</script>
</body>
</html>
