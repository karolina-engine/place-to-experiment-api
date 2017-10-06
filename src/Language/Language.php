<?php
namespace Karolina\Language;

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\MessageSelector;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Loader\YamlFileLoader;

class Language
{
    private $translator;
    public function __construct($lang)
    {
        $translator = new Translator($lang, new MessageSelector());
        $translator->addLoader('yaml', new YamlFileLoader());
        
        // TODO: These references should probably not be loaded from here, rather passed on higher up

        $translator->setFallbackLocales(array('en'));

        $this->translator = $translator;
        $end = microtime(true);
    }

    public function trans($key, $vars = [], $domain = null, $locale = null)
    {
        return $this->translator->trans($key, $vars, $domain, $locale);
    }

    /*
    public function template () {

        $loader = new \Twig_Loader_Array(array(
            'index' => "{% trans with {'%something%': 'Symfony'} %}something.great{% endtrans %}",
        ));

        //pass loader to new twig environment
        $twig = new \Twig_Environment($loader);
        $twig->addExtension(new \Symfony\Bridge\Twig\Extension\TranslationExtension($this->translator));
        return $twig->render('index');


    }
    */
}
