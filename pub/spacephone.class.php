<?php

// Template library
require_once('h2o.php');

// Markdown library
require_once('Parsedown.php');


class SpacePhone {
    public $options = array();
    public $root;
    public $html;

    private $_template;
    private $_contents;
    private $_markdown;

    private $_dialpad = array(
        '0',
        '1/.@',
        '2abc',
        '3def',
        '4ghi',
        '5jkl',
        '6mno',
        '7pqrs',
        '8tuv',
        '9wxyz',
    );

    function __construct($options, $root = null, $html = null) {
        $this->options = $this->getOptions($options);
        $this->root = realpath(is_null($root) ? dirname(__DIR__) : $root);
        $this->html = realpath(is_null($html) ? __DIR__ : $html);

        // Page contents folder
        $this->_contents = join('/', array($this->html, 'page'));

        // Templates root folder
        $this->_template = join('/', array($this->html, 'template'));

        // Markdown parser instance
        $this->_markdown = new Parsedown();
    }

    static function getOptions($options = array()) {
        return array_merge(array(
            'cache_ttl' => 300,
        ), $options);
    }

    public function config() {
        echo $this->template('spacephone/config.html');
    }

    public function error($code = 500, $context = array()) {
        http_response_code($code);
        switch ($code) {
        case 404:
            die($this->page('404', $context));
        default:
            die($this->page('error', $context));
        }
    }

    /**
     * Handle the HTTP request
     */
    public function handle() {
        $base = trim(@$_SERVER['REQUEST_URI'], '/');
        $part = explode('/', $base);

        try {
            switch ($part[0]) {
            case '/':
            case '':
                if (count($part) > 1)
                    $this->error(404);
                else
                    $this->page('index');
                break;

            case 'about':
                if (count($part) > 1)
                    $this->error(404);
                else
                    $this->page('about');
                break;

            case 'config':
                $this->config();
                break;

            case 'lookup':
                if (count($part) > 1)
                    $this->lookup_number($part[1]);
                else
                    $this->lookup();
                break;

            default:
                $this->error(404);
            }
        } catch (Exception $e) {
            $this->error(500, array('error' => $e));
        }
    }

    public function lookup() {
        $q = filter_input(INPUT_GET, 'q', FILTER_SANITIZE_ENCODED);
        if (strlen($q) > 0) {
            header('Location: /lookup/' . $this->_tonumber($q));
            exit;
        }

        header('Location: /');
        exit;
    }

    public function lookup_number($number) {
        if (strlen($number) == 0) {
            header('Location: /lookup/');
            exit;
        }
        $normalized = $this->_tonumber($number);
        if ($number != $normalized) {
            header('Location: /lookup/' . $normalized);
            exit;
        }

        $enum_number = '+' . $number;
        $enum_domain = $this->_e164_domain($number);

        // Lazy admin was lazy (or did not want a default)
        if (!strlen($enum_domain)) {
            $this->error(404, array('error' => 'No zone found for number'));
            exit;
        }

        // Lookup the NAPTR bits for this record
        $resolver = $this->_resolver_for($enum_domain);
        $dns_tree = array();
        $dns_name = $this->_e164_fqdn($number, $enum_domain);
        $dns_part = explode('.', $dns_name);
        $dns_ptrs = array();

        $rrs = dns_get_record($dns_name, DNS_NAPTR, $resolver);
        foreach ($rrs as $rr) {
            $dns_ptrs[$rr['services']] = $this->_e2u_link(
                $rr['services'],
                $this->_dns_naptr_result($dns_name, $enum_domain, $rr)
            );
        }

        // Lookup the root zone for this record
        $dns_root = null;
        for ($i = 0, $l = count($dns_part); $i < $l; ++$i) {
            if (!is_numeric($dns_part[$i])) {
                $dns_root = implode('.', array_slice($dns_part, $i));
                break;
            }
        }

        if (!is_null($dns_root)) {
            for ($i = 0, $l = count($dns_part); $i < $l; ++$i) {
                $dns_record = implode('.', array_slice($dns_part, $i));
                try {
                    $rrs = dns_get_record($dns_record, DNS_SOA, $resolver);
                    foreach ($rrs as $rr) {
                        if ($rr['type'] == 'SOA' && isset($rr['rname']) && !empty($rr['rname'])) {
                            $email = implode('@', explode('.', $rr['rname'], 2));
                            $dns_tree[$dns_record] = array(
                                'email' => $email,
                            );
                        }
                    }
                } catch (Exception $e) {
                }

                if ($dns_record == $dns_root) {
                    break;
                }
            }
        }

        echo $this->template('spacephone/lookup.html', array(
            'enum_number' => $enum_number,
            'enum_domain' => $enum_domain,
            'dns_name'    => $dns_name,
            'dns_ptrs'    => $dns_ptrs,
            'dns_tree'    => $dns_tree,
        ));
    }

    public function page($name, $context = array()) {
        $path = realpath(join('/', array($this->_contents, $name . '.md')));
        $root = $this->_contents;

        // Check if the requested path is legal
        if (strcmp($root, substr($path, 0, strlen($root))) !== 0) {
            $this->error(500);
        }

        // Check if the requested path is accessible
        if (!is_readable($path) || !is_readable($path)) {
            $this->error(404);
        }

        $data = file_get_contents($path);
        $page = array_merge($context, array(
            'page' => $name,
            'text' => H2o::parseString($this->_markdown->text($data)),
        ));
        $page['text'] = $page['text']->render($page);

        echo $this->template('spacephone/page.html', $page);
    }

    public function template($name, $context = array()) {
        $context = array_merge(array(
            'navigation' => array(
                '/'        => 'Home',
                '/about/'  => 'About',
                '/options/' => 'Configuration',
            ),
            'request'    => $this->_request(),
        ), $context);

        $h2o = new H2o($name, array(
            'cache_ttl'  => $this->options['cache_ttl'],
            'searchpath' => array($this->_template . '/'),
        ));
        return $h2o->render($context);
    }

    /**
     * Evaluate a DNS NAPTR result record
     */
    private function _dns_naptr_result($name, $zone, $rr) {
        list ($_, $pattern, $replace, $_) = explode('!', $rr['regex']);
        try {
            $number = '+' . strrev(str_replace('.', '', str_replace($zone, '', $name)));
            $result = preg_replace('/' . $pattern . '/', $replace, $number);
            return $result;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Returns the parent domain for a given telephone number.
     */
    private function _e164_domain($number) {
        foreach ($this->options['e164_zones'] as $prefix => $zone) {
            if (strpos((string) $number, (string) $prefix) === 0) {
                return $zone;
            }
        }

        return @$this->options['e164_zones']['default'];
    }

    /**
     * Returns the canonical E.164 FQDN for a number in a given zone.
     */
    private function _e164_fqdn($number, $zone = 'e164.arpa') {
        $number = (string) $number;
        $fqdn = array($zone);
        for ($i = 0, $l = strlen($number); $i < $l; ++$i) {
            $fqdn[] = (string) $number[$i];
        }
        return implode('.', array_reverse($fqdn));
    }

    /**
     * Returns the HTML link form of an E2U.
     */
    private function _e2u_link($type, $content) {
        switch (strtolower($type)) {
        case 'e2u+cnam':
            return str_replace('data:,', '', $content);
        case 'e2u+sip':
            return sprintf('<a href="sip:%s">%s</a>', $content, htmlentities($content));
        case 'e2u+web:http':
        case 'e2u+web:https':
            return sprintf('<a href="%s">%s</a>', $content, htmlentities($content));
        default:
            return htmlentities($content);
        }
    }

    /**
     * Returns a WSGI-like request object.
     *
     * Contains sufficient variables to satisfy the requirements of our templates.
     */
    private function _request() {
        return array(
            'path' => @$_SERVER['REQUEST_URI'],
        );
    }

    /**
     * Returns a DNS resolver for the given ENUM domain.
     */
    private function _resolver_for($domain) {
        if (isset($this->options['resolvers'][$domain])) {
            return $this->options['resolvers'][$domain];
        } else {
            return $this->options['resolvers']['default'];
        }
    }

    /**
     * Convert any string to a telephone number.
     */
    private function _tonumber($input) {
        $number = array();

        for ($i = 0, $l = strlen($input); $i < $l; ++$i) {
            $char = strtolower($input[$i]);
            foreach ($this->_dialpad as $index => $group) {
                if (strpos($group, $char) !== false) {
                    array_push($number, $index);
                }
            }
        }

        return implode("", $number);
    }
}
