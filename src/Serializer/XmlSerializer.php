<?php

namespace MintWare\DMM\Serializer;

class XmlSerializer implements SerializerInterface
{

    /** @inheritdoc */
    public function deserialize($data)
    {
        $domDoc = new \DOMDocument();
        $domDoc->loadXML($data);

        /** @var \DOMElement $el */
        $el = $domDoc->childNodes[0];
        $fetched = $this->handleElement($el);
        $field = new DeserializedField($fetched[0], $this->getAttributes($el));
        return $field;
    }

    private function handleElement(\DOMElement $element)
    {
        $out = [];
        $attributes = $this->getAttributes($element);

        /**
         * @var  $name
         * @var \DOMElement $node
         */
        foreach ($element->childNodes as $node) {

            if ($node->localName == null && $node->parentNode == null) continue;
            $name = $node->nodeName;
            if ($element->childNodes->length > 1 && $name == '#text') continue;

            if ($node->hasChildNodes()) {
                $tmpHandled = $this->handleElement($node);
                if (is_array($tmpHandled[0]) && isset($tmpHandled[0]['#text'])) {
                    if (count($tmpHandled[0]) == 1) {
                        $tmpHandled[0] = $tmpHandled[0]['#text'];
                    } else {
                        unset($tmpHandled[0]['#text']);
                    }
                }
                if (isset($out[$name]) && $out[$name] instanceof DeserializedField) {
                    $out[$name] = [$out[$name]];
                    $out[$name][] = new DeserializedField($tmpHandled[0], $tmpHandled[1]);
                } elseif (isset($out[$name])) {
                    $out[$name][] = new DeserializedField($tmpHandled[0], $tmpHandled[1]);
                } else {
                    $out[$name] = new DeserializedField($tmpHandled[0], $tmpHandled[1]);
                }
            } else {
                $out[$name] = $node->nodeValue;
            }
        }

        return [$out, $attributes];
    }

    /** @inheritdoc */
    public function serialize($data)
    {
        // TODO: Implement serialize() method.
    }

    /**
     * @param \DOMElement $element
     * @return array
     */
    private function getAttributes(\DOMElement $element)
    {
        $attributes = [];
        if ($element->hasAttributes()) {
            /** @var \DOMNode $attribute */
            foreach ($element->attributes as $attribute) {
                $attributes[$attribute->nodeName] = $attribute->nodeValue;
            }
        }
        return $attributes;
    }
}