# Internal Error

The document you requested at ``{{ request.path }}`` generated an internal error.

![Something went wrong there...](/static/img/404.png)

{% if error %}
Additionally, the following error was reported: ``{{ error }}``
{% endif %}
