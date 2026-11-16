import assert from 'node:assert/strict';
import fs from 'node:fs/promises';
import { chromium, expect } from '@playwright/test';
const origin='http://127.0.0.1:3000', api='http://localhost:8000';
const browser=await chromium.launch();
try {
  const page=await browser.newPage({viewport:{width:393,height:852},reducedMotion:'reduce'});
  await page.route('**/api/**',route=>['GET','HEAD'].includes(route.request().method())?route.continue():route.abort());
  const catalog=await(await page.request.get(api+'/api/api-products')).json();
  let product;
  for(const item of catalog.data.slice(0,20)) {
    const response=await page.request.get(api+'/api/api-products/'+encodeURIComponent(item.product_id));
    const detail=(await response.json())[0];
    if(detail?.Description_images?.length){product=detail;break;}
  }
  assert.ok(product,'A real active product with admin-uploaded Description Images is required.');
  const href='/products/'+product.product_name.toLowerCase().replace(/[^a-z0-9]+/g,'-')+'?'+new URLSearchParams({id:product.product_id});
  const errors=[];page.on('pageerror',error=>errors.push(error.message));
  await fs.mkdir('artifacts/description-images',{recursive:true});
  const results=[];
  for(const width of [360,393,768,1440]) {
    await page.setViewportSize({width,height:900});await page.goto(origin+href,{waitUntil:'networkidle'});
    const section=page.getByRole('region',{name:'Detailed description'});await expect(section).toBeVisible();
    await expect(section.locator('.description-image-button')).toHaveCount(new Set(product.Description_images).size);
    await section.scrollIntoViewIfNeeded();
    const dimensions=await section.locator('.description-image-button img').evaluateAll(async nodes=>{
      await Promise.all(nodes.map(async img=>{img.loading='eager';await img.decode();}));
      return nodes.map(img=>({naturalWidth:img.naturalWidth,naturalHeight:img.naturalHeight,width:img.getBoundingClientRect().width,height:img.getBoundingClientRect().height}));
    });
    for(const image of dimensions){assert.ok(image.naturalWidth>0);assert.ok(Math.abs(image.width/image.height-image.naturalWidth/image.naturalHeight)<.02);}
    assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1),true);
    if(product.health_benefit) await expect(section.locator('.product-description-text')).toHaveCSS('white-space','pre-line');
    await section.screenshot({path:`artifacts/description-images/details-${width}.png`,style:".site-header,.skip-link,.purchase-buy-row,.mobile-nav{visibility:hidden!important}"});
    await section.getByRole('button').first().click();
    const modal=page.getByRole('dialog',{name:/description image 1$/});await expect(modal).toBeVisible();
    await expect(modal.locator('img')).toHaveAttribute('src',api+'/storage/'+product.Description_images[0]);
    await page.screenshot({path:`artifacts/description-images/enlarged-${width}.png`});await page.keyboard.press('Escape');await expect(modal).not.toBeVisible();
    results.push({width,descriptionImages:dimensions.length,loaded:true,naturalProportions:true,zoom:true,overflow:false});
  }
  assert.deepEqual(errors,[]);await fs.writeFile('artifacts/description-images/results.json',JSON.stringify({product:product.product_name,results,scriptErrors:errors},null,2));
  console.log('PASS: real description text/images, natural proportions, image enlargement and no overflow at 360/393/768/1440px.');
}finally{await browser.close();}
