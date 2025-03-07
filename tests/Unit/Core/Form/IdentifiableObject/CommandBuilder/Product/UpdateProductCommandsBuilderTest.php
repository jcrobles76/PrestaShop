<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace Tests\Unit\Core\Form\IdentifiableObject\CommandBuilder\Product;

use PrestaShop\PrestaShop\Core\Domain\Product\Command\UpdateProductCommand;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductCondition;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\ProductVisibility;
use PrestaShop\PrestaShop\Core\Domain\Product\ValueObject\RedirectType;
use PrestaShop\PrestaShop\Core\Domain\Shop\ValueObject\ShopConstraint;
use PrestaShop\PrestaShop\Core\Form\IdentifiableObject\CommandBuilder\Product\UpdateProductCommandsBuilder;

class UpdateProductCommandsBuilderTest extends AbstractProductCommandBuilderTest
{
    /**
     * @dataProvider getExpectedCommands
     *
     * @param array $formData
     * @param array $expectedCommands
     */
    public function testBuildCommand(array $formData, array $expectedCommands)
    {
        $builder = new UpdateProductCommandsBuilder(self::MODIFY_ALL_SHOPS_PREFIX);
        $builtCommands = $builder->buildCommands($this->getProductId(), $formData, $this->getSingleShopConstraint());
        $this->assertEquals($expectedCommands, $builtCommands);
    }

    /**
     * @return iterable
     */
    public function getExpectedCommands(): iterable
    {
        yield [
            [
                'specifications' => [
                    'condition' => null,
                ],
            ],
            [],
        ];

        yield [
            [
                'no_data' => ['useless value'],
            ],
            [],
        ];

        yield [
            [
                'description' => [
                    'not_handled' => 0,
                ],
            ],
            [],
        ];

        $command = $this->getSingleShopCommand();
        $localizedNames = [
            1 => 'Nom français',
            2 => 'French name',
        ];
        $command->setLocalizedNames($localizedNames);
        yield [
            [
                'header' => [
                    'name' => $localizedNames,
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $localizedDescriptions = [
            1 => 'Description française',
            2 => 'English description',
        ];
        $command->setLocalizedDescriptions($localizedDescriptions);
        yield [
            [
                'description' => [
                    'description' => $localizedDescriptions,
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $localizedShortDescriptions = [
            1 => 'Résumé français',
            2 => 'English summary',
        ];
        $command->setLocalizedShortDescriptions($localizedShortDescriptions);
        yield [
            [
                'description' => [
                    'description_short' => $localizedShortDescriptions,
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setManufacturerId(1);
        yield [
            [
                'description' => [
                    'manufacturer' => '1',
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setCondition(ProductCondition::NEW);
        yield [
            [
                'specifications' => [
                    'condition' => 'new',
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setShowCondition(true);
        yield [
            [
                'specifications' => [
                    'not_handled' => 0,
                    'show_condition' => 1,
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setOnlineOnly(true);
        yield [
            [
                'options' => [
                    'not_handled' => 0,
                    'visibility' => [
                        'online_only' => true,
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setShowPrice(false);
        yield [
            [
                'options' => [
                    'not_handled' => 0,
                    'visibility' => [
                        'show_price' => false,
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setAvailableForOrder(true);
        yield [
            [
                'options' => [
                    'not_handled' => 0,
                    'visibility' => [
                        'available_for_order' => '1',
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setVisibility(ProductVisibility::INVISIBLE);
        yield [
            [
                'options' => [
                    'not_handled' => 0,
                    'visibility' => [
                        'visibility' => ProductVisibility::INVISIBLE,
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setPrice('45.56');
        yield [
            [
                'pricing' => [
                    'not_handled' => 0,
                    'retail_price' => [
                        'price_tax_excluded' => 45.56,
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setPrice('45.56');
        yield [
            [
                'pricing' => [
                    'not_handled' => 0,
                    'retail_price' => [
                        'price_tax_excluded' => '45.56',
                        'price_tax_included' => '65.56', // Price tax included is ignored
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setEcotax('45.56');
        yield [
            [
                'pricing' => [
                    'not_handled' => 0,
                    'retail_price' => [
                        'ecotax_tax_excluded' => '45.56',
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setTaxRulesGroupId(42);
        yield [
            [
                'pricing' => [
                    'not_handled' => 0,
                    'retail_price' => [
                        'tax_rules_group_id' => '42',
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setOnSale(true);
        yield [
            [
                'pricing' => [
                    'not_handled' => 0,
                    'on_sale' => '42',
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setOnSale(false);
        yield [
            [
                'pricing' => [
                    'not_handled' => 0,
                    'on_sale' => '0',
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setWholesalePrice('45.56');
        yield [
            [
                'pricing' => [
                    'not_handled' => 0,
                    'wholesale_price' => '45.56',
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setUnitPrice('45.56');
        yield [
            [
                'pricing' => [
                    'not_handled' => 0,
                    'unit_price' => [
                        'price_tax_excluded' => '45.56',
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setUnity('kg');
        yield [
            [
                'pricing' => [
                    'not_handled' => 0,
                    'unit_price' => [
                        'unity' => 'kg',
                    ],
                ],
            ],
            [$command],
        ];

        $localizedMetaTitles = [
            1 => 'Titre français recherche',
            2 => 'English title seo',
        ];

        $localizedMetaDescriptions = [
            1 => 'Description française recherche',
            2 => 'English description seo',
        ];

        $localizedLinkRewrites = [
            1 => 'produit-francais',
            2 => 'english-product',
        ];

        $command = $this
            ->getSingleShopCommand()
            ->setLocalizedMetaTitles($localizedMetaTitles)
        ;
        yield 'meta title' => [
            [
                'seo' => [
                    'meta_title' => $localizedMetaTitles,
                ],
            ],
            [$command],
        ];

        $command = $this
            ->getSingleShopCommand()
            ->setLocalizedMetaDescriptions($localizedMetaDescriptions)
        ;
        yield 'meta description' => [
            [
                'seo' => [
                    'meta_description' => $localizedMetaDescriptions,
                ],
            ],
            [$command],
        ];

        $command = $this
            ->getSingleShopCommand()
            ->setLocalizedLinkRewrites($localizedLinkRewrites)
        ;
        yield 'link rewrite' => [
            [
                'seo' => [
                    'link_rewrite' => $localizedLinkRewrites,
                ],
            ],
            [$command],
        ];

        $command = $this
            ->getSingleShopCommand()
            ->setRedirectOption(RedirectType::TYPE_NOT_FOUND, 0)
        ;
        yield 'redirect not found' => [
            [
                'seo' => [
                    'redirect_option' => [
                        'type' => RedirectType::TYPE_NOT_FOUND,
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this
            ->getSingleShopCommand()
            ->setRedirectOption(RedirectType::TYPE_PRODUCT_TEMPORARY, 42)
        ;
        yield 'redirect to product' => [
            [
                'seo' => [
                    'redirect_option' => [
                        'type' => RedirectType::TYPE_PRODUCT_TEMPORARY,
                        'target' => [
                            'id' => 42,
                        ],
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this
            ->getSingleShopCommand()
            ->setRedirectOption(RedirectType::TYPE_CATEGORY_TEMPORARY, 51)
        ;
        yield 'redirect to category' => [
            [
                'seo' => [
                    'redirect_option' => [
                        'type' => RedirectType::TYPE_CATEGORY_TEMPORARY,
                        'target' => [
                            'id' => 51,
                        ],
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setReference('ref');
        yield [
            [
                'specifications' => [
                    'references' => [
                        'reference' => 'ref',
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setIsbn('0-8044-2957-X');
        yield [
            [
                'specifications' => [
                    'references' => [
                        'isbn' => '0-8044-2957-X',
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setEan13('13');
        yield [
            [
                'specifications' => [
                    'references' => [
                        'ean_13' => '13',
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setUpc('1345');
        yield [
            [
                'specifications' => [
                    'references' => [
                        'upc' => '1345',
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command->setMpn('mpn');
        yield [
            [
                'specifications' => [
                    'references' => [
                        'mpn' => 'mpn',
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand()
            ->setVisibility(ProductVisibility::INVISIBLE)
            ->setLocalizedShortDescriptions($localizedShortDescriptions)
            ->setLocalizedDescriptions($localizedDescriptions)
            ->setLocalizedNames($localizedNames)
            ->setUnity('pounds')
            ->setUnitPrice('45.56')
            ->setWholesalePrice('70.05')
            ->setEcotax('60.43')
            ->setTaxRulesGroupId(43)
            ->setLocalizedMetaTitles($localizedMetaTitles)
            ->setLocalizedMetaDescriptions($localizedMetaDescriptions)
            ->setLocalizedLinkRewrites($localizedLinkRewrites)
            ->setRedirectOption(RedirectType::TYPE_PRODUCT_TEMPORARY, 42)
            ->setIsbn('0-8044-2957-X')
            ->setEan13('13')
            ->setUpc('1345')
            ->setMpn('mpn')
            ->setReference('0123456789')
        ;

        yield [
            [
                'header' => [
                    'name' => $localizedNames,
                ],
                'description' => [
                    'description_short' => $localizedShortDescriptions,
                    'description' => $localizedDescriptions,
                ],
                'options' => [
                    'not_handled' => 0,
                    'visibility' => [
                        'visibility' => ProductVisibility::INVISIBLE,
                    ],
                ],
                'pricing' => [
                    'unit_price' => [
                        'unity' => 'pounds',
                        'price_tax_excluded' => '45.56',
                        'price_tax_included' => '65.56', // Price tax included is ignored
                    ],
                    'wholesale_price' => '70.05',
                    'on_sale' => false,
                    'retail_price' => [
                        'ecotax_tax_excluded' => '60.43',
                        'tax_rules_group_id' => '43',
                    ],
                ],
                'seo' => [
                    'meta_title' => $localizedMetaTitles,
                    'meta_description' => $localizedMetaDescriptions,
                    'link_rewrite' => $localizedLinkRewrites,
                    'redirect_option' => [
                        'type' => RedirectType::TYPE_PRODUCT_TEMPORARY,
                        'target' => [
                            'id' => 42,
                        ],
                    ],
                ],
                'specifications' => [
                    'references' => [
                        'isbn' => '0-8044-2957-X',
                        'ean_13' => '13',
                        'upc' => '1345',
                        'mpn' => 'mpn',
                        'reference' => '0123456789',
                    ],
                ],
            ],
            [$command],
        ];
    }

    /**
     * @dataProvider getExpectedCommandsMultiShop
     *
     * @param array $formData
     * @param array $expectedCommands
     */
    public function testBuildCommandMultiShop(array $formData, array $expectedCommands): void
    {
        $builder = new UpdateProductCommandsBuilder(self::MODIFY_ALL_SHOPS_PREFIX);
        $builtCommands = $builder->buildCommands($this->getProductId(), $formData, $this->getSingleShopConstraint());
        $this->assertEquals($expectedCommands, $builtCommands);
    }

    /**
     * @return iterable
     */
    public function getExpectedCommandsMultiShop(): iterable
    {
        $localizedNames = [
            1 => 'Nom français',
            2 => 'French name',
        ];
        $localizedDescriptions = [
            1 => 'Description française',
            2 => 'English description',
        ];
        $localizedShortDescriptions = [
            1 => 'Résumé français',
            2 => 'English summary',
        ];

        $command = $this->getAllShopsCommand();
        $command
            ->setLocalizedNames($localizedNames)
            ->setLocalizedDescriptions($localizedDescriptions)
            ->setLocalizedShortDescriptions($localizedShortDescriptions)
        ;
        yield [
            [
                'header' => [
                    'name' => $localizedNames,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'name' => true,
                ],
                'description' => [
                    'description' => $localizedDescriptions,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'description' => true,
                    'description_short' => $localizedShortDescriptions,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'description_short' => true,
                ],
            ],
            [$command],
        ];

        $command = $this->getSingleShopCommand();
        $command
            ->setLocalizedNames($localizedNames)
            ->setLocalizedDescriptions($localizedDescriptions)
            ->setLocalizedShortDescriptions($localizedShortDescriptions)
        ;
        yield [
            [
                'header' => [
                    'name' => $localizedNames,
                ],
                'description' => [
                    'description' => $localizedDescriptions,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'description' => false,
                    'description_short' => $localizedShortDescriptions,
                ],
            ],
            [$command],
        ];

        $allShopsCommand = $this->getAllShopsCommand();
        $singleCommand = $this->getSingleShopCommand();
        $singleCommand
            ->setLocalizedDescriptions($localizedDescriptions)
        ;
        $allShopsCommand
            ->setLocalizedNames($localizedNames)
            ->setLocalizedShortDescriptions($localizedShortDescriptions)
        ;
        yield [
            [
                'header' => [
                    'name' => $localizedNames,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'name' => true,
                ],
                'description' => [
                    'description' => $localizedDescriptions,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'description' => false,
                    'description_short' => $localizedShortDescriptions,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'description_short' => true,
                ],
            ],
            [$singleCommand, $allShopsCommand],
        ];

        $allShopsCommand = $this->getAllShopsCommand();
        $singleCommand = $this->getSingleShopCommand();
        $singleCommand
            ->setLocalizedNames($localizedNames)
            ->setLocalizedShortDescriptions($localizedShortDescriptions)
        ;
        $allShopsCommand
            ->setLocalizedDescriptions($localizedDescriptions)
        ;
        yield [
            [
                'header' => [
                    'name' => $localizedNames,
                ],
                'description' => [
                    'description' => $localizedDescriptions,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'description' => true,
                    'description_short' => $localizedShortDescriptions,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'description_short' => false,
                ],
            ],
            [$singleCommand, $allShopsCommand],
        ];

        $command = $this->getAllShopsCommand();
        $command->setCondition(ProductCondition::NEW);
        yield [
            [
                'specifications' => [
                    'condition' => 'new',
                    self::MODIFY_ALL_SHOPS_PREFIX . 'condition' => true,
                ],
            ],
            [$command],
        ];

        $command = $this->getAllShopsCommand();
        $command->setShowCondition(false);
        yield [
            [
                'specifications' => [
                    'not_handled' => 0,
                    'show_condition' => 0,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'show_condition' => true,
                ],
            ],
            [$command],
        ];

        $command = $this->getAllShopsCommand();
        $command->setShowPrice(false);
        yield [
            [
                'options' => [
                    'not_handled' => 0,
                    'visibility' => [
                        'show_price' => false,
                        self::MODIFY_ALL_SHOPS_PREFIX . 'show_price' => true,
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this->getAllShopsCommand();
        $command->setAvailableForOrder(true);
        yield [
            [
                'options' => [
                    'visibility' => [
                        'available_for_order' => '1',
                        self::MODIFY_ALL_SHOPS_PREFIX . 'available_for_order' => true,
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this->getAllShopsCommand();
        $command->setVisibility(ProductVisibility::INVISIBLE);
        yield [
            [
                'options' => [
                    'visibility' => [
                        'visibility' => ProductVisibility::INVISIBLE,
                        self::MODIFY_ALL_SHOPS_PREFIX . 'visibility' => true,
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this->getAllShopsCommand();
        $command->setOnlineOnly(false);
        yield [
            [
                'options' => [
                    'visibility' => [
                        'online_only' => false,
                        self::MODIFY_ALL_SHOPS_PREFIX . 'online_only' => true,
                    ],
                ],
            ],
            [$command],
        ];

        $singleShopCommand = $this->getSingleShopCommand();
        $singleShopCommand->setVisibility(ProductVisibility::VISIBLE_EVERYWHERE);
        $allShopsCommand = $this->getAllShopsCommand();
        $allShopsCommand->setAvailableForOrder(true);
        yield [
            [
                'options' => [
                    'not_handled' => 0,
                    'visibility' => [
                        'visibility' => ProductVisibility::VISIBLE_EVERYWHERE,
                        'available_for_order' => true,
                        self::MODIFY_ALL_SHOPS_PREFIX . 'available_for_order' => true,
                    ],
                ],
            ],
            [$singleShopCommand, $allShopsCommand],
        ];

        $localizedMetaTitles = [
            1 => 'Titre français recherche',
            2 => 'English title seo',
        ];

        $localizedMetaDescriptions = [
            1 => 'Description française recherche',
            2 => 'English description seo',
        ];

        $command = $this
            ->getSingleShopCommand()
            ->setLocalizedMetaTitles($localizedMetaTitles)
        ;
        yield 'meta title' => [
            [
                'seo' => [
                    'meta_title' => $localizedMetaTitles,
                ],
            ],
            [$command],
        ];

        $command = $this
            ->getSingleShopCommand()
            ->setLocalizedMetaDescriptions($localizedMetaDescriptions)
        ;
        yield 'meta description' => [
            [
                'seo' => [
                    'meta_description' => $localizedMetaDescriptions,
                ],
            ],
            [$command],
        ];

        $localizedLinkRewrites = [
            1 => 'produit-francais',
            2 => 'english-product',
        ];
        $command = $this
            ->getSingleShopCommand()
            ->setLocalizedLinkRewrites($localizedLinkRewrites)
        ;
        yield 'link rewrite' => [
            [
                'seo' => [
                    'link_rewrite' => $localizedLinkRewrites,
                ],
            ],
            [$command],
        ];

        $command = $this
            ->getSingleShopCommand()
            ->setRedirectOption(RedirectType::TYPE_NOT_FOUND, 0)
        ;
        yield 'redirect not found' => [
            [
                'seo' => [
                    'redirect_option' => [
                        'type' => RedirectType::TYPE_NOT_FOUND,
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this
            ->getSingleShopCommand()
            ->setRedirectOption(RedirectType::TYPE_PRODUCT_TEMPORARY, 42)
        ;
        yield 'redirect to product' => [
            [
                'seo' => [
                    'redirect_option' => [
                        'type' => RedirectType::TYPE_PRODUCT_TEMPORARY,
                        'target' => [
                            'id' => 42,
                        ],
                    ],
                ],
            ],
            [$command],
        ];

        $command = $this
            ->getSingleShopCommand()
            ->setRedirectOption(RedirectType::TYPE_CATEGORY_TEMPORARY, 51)
        ;
        yield 'redirect to category' => [
            [
                'seo' => [
                    'redirect_option' => [
                        'type' => RedirectType::TYPE_CATEGORY_TEMPORARY,
                        'target' => [
                            'id' => 51,
                        ],
                    ],
                ],
            ],
            [$command],
        ];

        $singleShopCommand = $this->getSingleShopCommand();
        $singleShopCommand
            ->setVisibility(ProductVisibility::VISIBLE_EVERYWHERE)
            ->setLocalizedShortDescriptions($localizedShortDescriptions)
            ->setLocalizedMetaTitles($localizedMetaTitles)
            ->setLocalizedLinkRewrites($localizedLinkRewrites)
        ;
        $allShopsCommand = $this->getAllShopsCommand();
        $allShopsCommand
            ->setAvailableForOrder(true)
            ->setLocalizedNames($localizedNames)
            ->setLocalizedDescriptions($localizedDescriptions)
            ->setLocalizedMetaDescriptions($localizedMetaDescriptions)
            ->setRedirectOption(RedirectType::TYPE_PRODUCT_TEMPORARY, 42)
        ;

        yield [
            [
                'header' => [
                    'name' => $localizedNames,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'name' => true,
                ],
                'description' => [
                    'description' => $localizedDescriptions,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'description' => true,
                    'description_short' => $localizedShortDescriptions,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'description_short' => false,
                ],
                'options' => [
                    'not_handled' => 0,
                    'visibility' => [
                        'visibility' => ProductVisibility::VISIBLE_EVERYWHERE,
                        'available_for_order' => true,
                        self::MODIFY_ALL_SHOPS_PREFIX . 'available_for_order' => true,
                    ],
                ],
                'seo' => [
                    'meta_title' => $localizedMetaTitles,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'meta_title' => false,
                    'meta_description' => $localizedMetaDescriptions,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'meta_description' => true,
                    'link_rewrite' => $localizedLinkRewrites,
                    self::MODIFY_ALL_SHOPS_PREFIX . 'link_rewrite' => false,
                    'redirect_option' => [
                        'type' => RedirectType::TYPE_PRODUCT_TEMPORARY,
                        'target' => [
                            'id' => 42,
                            self::MODIFY_ALL_SHOPS_PREFIX . 'id' => true,
                        ],
                    ],
                ],
            ],
            [$singleShopCommand, $allShopsCommand],
        ];
    }

    private function getSingleShopCommand(): UpdateProductCommand
    {
        return new UpdateProductCommand($this->getProductId()->getValue(), $this->getSingleShopConstraint());
    }

    private function getAllShopsCommand(): UpdateProductCommand
    {
        return new UpdateProductCommand($this->getProductId()->getValue(), ShopConstraint::allShops());
    }
}
