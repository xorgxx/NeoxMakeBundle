<?php

namespace NeoxMake\NeoxMakeBundle\Service;

use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatorInterface;

class NeoxTableTools
{

    /**
     * domaine translator.
     */
    protected ?string $domaine;

    private object $config ;

    /**
     * header title.
     */
    protected string $headerTitle;

    /**
     * set Css and js in twig template.
     */
    protected ?string $styling = null;

    /**
     * Set voter to true if the voter existe !!.
     */
    protected ?bool $voter = null;

    /**
     * header subTitle.
     */
    protected string $subTitle;

    /**
     * header table width for button action.
     *  <td style width:20%>
     */
    protected ?Int $tdWidth = 20;


    /**
     *  ex: {'class':'success', 'path':'path('seotag_new')', label:'Adddddd'},
     */
    protected ?array $button =[];

    /**
     * item to add header tr.
     * ex: {'Id', 'Balise', 'Property', 'Meta', 'Value'},
     */
    protected array $trThead;

    /**
     * header trBody.
     * ex: {'Id', 'Balise', 'Property', 'Meta', 'Value'},
     */
    protected ?string $trBody = null;

    /**
     * @var array|object
     */
    protected $entity;
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @return string|null
     */
    public function getDomaine(): ?string
    {
        return $this->domaine;
    }

    /**
     * @param string|null $domaine
     */
    public function setDomaine(?string $domaine): void
    {
        $this->domaine = $domaine;
    }

    public function getTranslator( string $key, ?string $subDomaine = "" ): string
    {
        $key = trim($key," ");

        switch (true) {
            case $subDomaine:
                $key = $this->translator->trans( $subDomaine . ".form.$key.label",[] ,$subDomaine) ? : $key;
                break;
            case (!strncmp("update", $key, strlen("update"))):
            case (!strncmp("creat", $key, strlen("creat"))):
                $key = $this->translator->trans("$key.label",[]) ? : $key;
                break;

            default:
                if ($this->getDomaine()) {
                    $key = $this->translator->trans( $this->getDomaine() . ".form.$key.label",[],$this->getDomaine()) ? : $key;
                }
        }

        return $key;
    }

    public function getConfigYaml( $pathYaml ): void
    {
        $this->config  =  Yaml::parseFile($pathYaml, Yaml::PARSE_OBJECT_FOR_MAP);
    }

    /**
     * @return string
     */
    public function getHeaderTitle(): string
    {
        return $this->headerTitle;
    }

    /**
     * @param string $headerTitle
     */
    public function setHeaderTitle(string $headerTitle): void
    {
        $this->headerTitle = $headerTitle;
    }

    /**
     * @return string|null
     */
    public function getStyling(): ?string
    {
        return $this->styling;
    }

    /**
     * @param string|null $styling
     */
    public function setStyling(?string $styling): void
    {
        $this->styling = $styling;
    }

    /**
     * @return bool|null
     */
    public function getVoter(): ?bool
    {
        return $this->voter;
    }

    /**
     * @param bool|null $voter
     */
    public function setVoter(?bool $voter): void
    {
        $this->voter = $voter;
    }


    /**
     * @return string
     */
    public function getSubTitle(): string
    {
        return $this->subTitle;
    }

    /**
     * @param string $subTitle
     */
    public function setSubTitle(string $subTitle): void
    {
        $this->subTitle = $subTitle;
    }

    /**
     * @return int
     */
    public function getTdWidth(): int
    {
        return $this->tdWidth;
    }

    /**
     * @param int $tdWidth
     */
    public function setTdWidth(int $tdWidth): self
    {
        $this->tdWidth = $tdWidth;
        return $this;
    }


    /**
     * @return array|null
     */
    public function getButton(): ?array
    {
        return $this->button;
    }

    /**
     * @param string|null $button
     * @param string|null $cat a = action bar; h = header bar
     * @param string|null $voter
     * @return NeoxTableTools
     */
    public function setButton(?string $button, ?string $cat = "a", ?string $voter = null): self
    {
        if ($voter) {
            $this->button[$cat][$voter] = $button;
        }else{
            $this->button[$cat][] = $button;
        }
        return $this;
    }


    /**
     * @return array
     */
    public function getTrThead(): array
    {
        return $this->trThead;
    }

    /**
     * @param string $trThead
     * @return NeoxTableBuilder
     */
    public function setTrThead(string $trThead): self
    {
        $items = explode(',', $trThead);
        foreach ($items as $key => $item) {
            // check domaine to translate relation entity
            [$subDomaine, $item, $elem] = $this->checkDomaineToTranslateRelationEntity($item);

            switch ($item) {
                case "@" :
                    break;
                case "#" :
                    $this->trThead[] = '<th class="">#</th>';
                    break;
                case "img" :
                    $this->trThead[] = '<th class="text-center">[#]</th>';
                    break;
                default :
                    // if word image is in then we add header image
                    $this->trThead[] = '<th class="">' . $this->getTranslator($elem, $subDomaine) . '</th>';
                    break;
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getTrBody(): string
    {
        return $this->trBody;
    }

    /**
     * @param string $trBody
     * @return NeoxTableTools
     */
    public function setTrBody(string $trBody): self
    {
        $items = explode(',', $trBody);
        foreach ($items as $key => $item) {
            // format item in good way !!!

            [$subDomaine, $item, $elem, $params] = $this->checkDomaineToTranslateRelationEntity($item);

            switch ($item) {
                case "@" :
                    break;
                case "#" :
                    $this->trBody .= '<td>{{ loop.index }}</td>';
                    break;
                case 'isVerified':
                    $this->trBody .= '<td style="text-align: center">
                            {% set color = item.isVerified ? "green" : "red" %}
                            <span style="color: {{color}}" class="bi-circle"></span>
                        </td>';
                    break;
                case "img" :
                    $this->trBody .= '<td style="text-align: center">
                            <img style="height: 100px" src="{{ vich_uploader_asset(item) }}" alt="dede">                           
                        </td>';
                    break;
                default :
                    $t = "{{ item.$item|default('null')|raw }}";

                    // check type -> date
                    if ($this->arrayInString("start,end,date,created,updated,lastConnect",$item)) {
                        $t = "{{ item.$item|format_datetime(locale='fr',pattern='EEEE dd MMMM YYYY') }}";
                    }

                    // convert date, dateTime in twig        tytyt@parking@time
                    if ($params) {
                        switch ("#".$params) {
                            case "#time":
                                $t =  "{{ item.$elem ? item.$elem|format_datetime(locale='fr',pattern='EEEE dd MMMM YYYY kk:mm') : item.$elem  }}";
                                break;
                            case "#date":
                                $t = "{{ item.$elem ? item.$elem|format_datetime(locale='fr',pattern='EEEE dd MMMM YYYY') : item.$elem }}";
                                break;
                            case "#enum":
                                $t = "{{ item.$elem|enum() }}";
                                break;
                            default:
                                $params     = str_replace("#","",$params);
                                $t          = "{{ item.$elem|$params }}";
                                break;
                        }
                    }

//
//                    if ($this->arrayInString("startTime,endTime",$item)) {
//                        $t =  "{{ item.$item|format_datetime(locale='fr',pattern='EEEE dd MMMM YYYY hh:mm') }}";
//                    }

                    $this->trBody .= '<td class=""> ' . $t . ' </td>';
//                    $this->trBody .= '<td class="">$t ? :{{ item.'.$item.' }}</td>';

                    break;
            }
        }
        return $this;
    }

    /**
     * @return array|object|null
     */
    public function getEntity(): object|array|null
    {
        return $this->entity;
    }

    /**
     * @param object|array $entity
     * @return NeoxTableTools
     */
    public function setEntity(object|array $entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * @param string $filters
     * @param $pathInfo
     * @return bool
     */
    private function arrayInString(string $filters, $pathInfo): bool
    {
        $filter = explode(",", $filters);
        foreach ($filter as $v) {
            if (!strncmp($v, $pathInfo, strlen($v)) or $pathInfo == $v ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $item
     * @return array
     */
    private function checkDomaineToTranslateRelationEntity(string $item): array
    {
        $subDomaine     = "";
        $elem           = $item;
        $params         = "";
        
        if (str_contains($item, "#")) {
            [$vachar, $params] = explode("#", $item, 2);
            [$elem, $subDomaine] = str_contains($vachar, "@") ? explode("@", $vachar, 2) : [$vachar, ""];
        }
        
        return [$subDomaine, $item, $elem, $params];



//        // civility@domain#enum
//        // author.email.label@user@time -> author.email.label ->  user -> email.label
//        $params     = strstr($item, '#');
//        // ->  user
//
//        $subDomaine = str_replace("@", "",strstr($item, '@')) ?: "";
//        $subDomaine = str_replace($params, "",$subDomaine);
//
//        // -> author.email.label
//        $item       = strstr($item, '@', true) ? : $item;
//
//        // -> email.label
//        $e          = strstr($item, '.',true);
//        $elem       = str_replace($e.".", "", $item);

//        return array($subDomaine, $item, $elem, $params);
    }
}