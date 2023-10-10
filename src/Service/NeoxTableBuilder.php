<?php

namespace NeoxMake\NeoxMakeBundle\Service;

use Symfony\UX\Turbo\TurboBundle;

class NeoxTableBuilder extends NeoxTableTools
{
//    public function __construct(TranslatorInterface $translator)
//    {
//        parent::__construct($translator);
//    }

    public function styling( ?string $pathTemplate=null): void
    {
        $this->setStyling($pathTemplate);
    }

    public function filterFields(string $fields, ?string $Domaine = null):self
    {
        // if domaine is set then we setup to translate
        $this->setDomaine($Domaine);

        //  removes whitespace or any other predefined character from both the left and right sides of a string.
        $fields = str_replace(' ', '', $fields);

        // create thead tr->th
        $this->setTrThead($fields);

        // Create tbody tr->td
        // lastConnect [@domain] [#params] lastConnect@parking#date
        $this->setTrBody($fields);
        return $this;
    }

    public function voterAcl( ?bool $bool) : self
    {
        $this->setVoter($bool);
        return $this;
    }

    public function setActButton(?string $action, ?string $header = null): self
    {
        $name = substr($action, 1, null);
        switch (true) {
            // header button default Add
            case str_starts_with( $action, "@" ):

                // ADDING button on header ==============================
                $header =  (new buttonBuild())
                    ->setLabel($this->getTranslator("add"))
                    ->setRef(" '{{path( '" . $name . "_new', {} )}}' " )
                    ->setClass("button-green")
                    ->setStyle("height: 30px", true)
                    ->setIcon("bi-plus-square-fill")
                    ->build();
                $this->setButton($header, "h");

                // ADDING button on action bar ============================
                $pin = (new buttonBuild())
                    ->setLabel("{{ (neoxTable.domaine~'.form.pin.publish.'~item.publish|default(0))|trans({}, neoxTable.domaine)}}")
                    ->setType("pin")
                    ->setRef(" {{path( '" . $name . "_pin', { 'id': item.id } )}} ", true )
                    ->setClass( " {{ item.publish is same as(true) ? 'button-dirtygreen' : 'button-info' }} ")
                    ->setIcon(" {{ item.publish is same as(true) ? 'bi-pin-fill' : 'bi-pin-angle-fill' }} ")
                    ->build();
                $this->setButton($pin, "a" ,"PIN");

                $edit = (new buttonBuild())
                    ->setRef(" '{{path( '" . $name . "_edit', { 'id': item.id } )}}' " )
                    ->setClass("button-green")
                    ->setIcon("fa-solid fa-file-edit")
                    ->build();
                $this->setButton($edit, "a" ,"EDIT");

                // ADDING !!! DELETE !!! button is special =================
                $del = (new buttonBuild())
                    ->setLabel($this->getTranslator("delete.msg"))
                    ->setType("delete")
                    ->setRef(" {{path( '" . $name . "_delete', { 'id': item.id } )}} ", true )
                    ->setClass("button-red")
                    ->setIcon("fa-solid fa-trash-alt")
                    ->build("admin/parameters/crud");
                $this->setButton($del, "a" ,"DELETE");
                break;
            // No header button !!
            case str_starts_with( $action, "#" ):
                // ADDING button on action bar ============================
                $pin = (new buttonBuild())
                    ->setLabel("{{ (neoxTable.domaine~'.form.pin.publish.'~item.publish|default(0))|trans({}, neoxTable.domaine)}}")
                    ->setType("pin")
                    ->setRef(" {{path( '" . $name . "_pin', { 'id': item.id } )}} ", true )
                    ->setClass( " {{ item.publish is same as(true) ? 'button-dirtygreen' : 'button-info' }} ")
                    ->setIcon(" {{ item.publish is same as(true) ? 'bi-pin-fill' : 'bi-pin-angle-fill' }} ")
                    ->build();
                $this->setButton($pin,"a" ,"PIN");

                $edit = (new buttonBuild())
                    ->setRef(" '{{path( '" . $name . "_edit', { 'id': item.id } )}}' " )
                    ->setClass("button-green")
                    ->setIcon("fa-solid fa-file-edit")
                    ->build();
                $this->setButton($edit, "a" ,"EDIT");

                // ADDING !!! DELETE !!! button is special =================
                $del = (new buttonBuild())
                    ->setLabel($this->getTranslator("delete.msg"))
                    ->setType("delete")
                    ->setRef(" {{path( '" . $name . "_delete', { 'id': item.id } )}} ", true )
                    ->setClass("button-red")
                    ->setIcon("fa-solid fa-trash-alt")
                    ->build("admin/parameters/crud");
                $this->setButton($del, "a" ,"DELETE");
                break;

            default :
                $this->setButton($action, $header);
        }
//        if ( str_starts_with( $action, "@" ) ) {
//
//
//        }else{
//            if ( $action ) {
//                $this->setButton($action, $header);
//            }
//
//        }

        return $this;
    }

    public static function checkTurbo($request, string $turboTemplate = null): bool
    {
        $r = false;
        // 🔥 The magic happens here! 🔥
        if ( TurboBundle::STREAM_FORMAT === $request->getPreferredFormat() ) {
            // If the request comes from Turbo, set the content type as text/vnd.turbo-stream.html and only send the HTML to update
            $request->setRequestFormat(TurboBundle::STREAM_FORMAT);
            $r = true;
        }
        return $r;
    }
}