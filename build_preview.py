import json

lokasi_peradilan = [
  {'nama':'PTA Medan','jenis':'PTA','alamat':'Jl. Kapten Sumarsono No. 12, Helvetia Timur, Medan Helvetia, Kota Medan 20124','lat':3.6315,'lng':98.6462},
  {'nama':'PA Medan (IA)','jenis':'PA','alamat':'Jl. Sisingamangaraja Km 8,8 No. 198, Medan Amplas','lat':3.5186,'lng':98.7188},
  {'nama':'PA Lubuk Pakam (IA)','jenis':'PA','alamat':'Jl. Mahoni No. 3, Kompleks Pemkab Deli Serdang','lat':3.5521,'lng':98.8559},
  {'nama':'PA Binjai','jenis':'PA','alamat':'Jl. Sultan Hasanuddin No. 24, Binjai Kota','lat':3.5979,'lng':98.4804},
  {'nama':'PA Stabat (IB)','jenis':'PA','alamat':'Jl. Proklamasi No. 46, Kwala Bingai, Stabat, Langkat','lat':3.7333,'lng':98.4500},
  {'nama':'PA Tanjung Balai','jenis':'PA','alamat':'Jl. Jend. Sudirman KM. 5.5, Tanjungbalai','lat':2.9667,'lng':99.8000},
  {'nama':'PA Kisaran (IA)','jenis':'PA','alamat':'Jl. Jend. Ahmad Yani No. 73, Kisaran, Asahan','lat':2.9845,'lng':99.6158},
  {'nama':'PA Tebing Tinggi','jenis':'PA','alamat':'Jl. Rumah Sakit Umum No. 7, Tebing Tinggi','lat':3.3250,'lng':99.1417},
  {'nama':'PA Pematang Siantar','jenis':'PA','alamat':'Jl. Sisingamangaraja–Pasar Baru No. 47, Nagahuta','lat':2.9537,'lng':99.0502},
  {'nama':'PA Simalungun','jenis':'PA','alamat':'Jl. Asahan Km 3,5, Nagori Pamatang Simalungun','lat':2.9400,'lng':99.0700},
  {'nama':'PA Sidikalang','jenis':'PA','alamat':'Jl. RSU No. 16, Batangberuh, Sidikalang, Dairi','lat':2.7380,'lng':98.3150},
  {'nama':'PA Kabanjahe','jenis':'PA','alamat':'Jl. Letjen Jamin Ginting, Kabanjahe, Karo','lat':3.1000,'lng':98.4900},
  {'nama':'PA Sei Rampah','jenis':'PA','alamat':'Jl. Negara, Desa Firdaus, Sei Rampah, Serdang Bedagai','lat':3.4575,'lng':99.1478},
  {'nama':'PA Balige','jenis':'PA','alamat':'Jl. Siborong-borong – Parapat, Balige, Toba','lat':2.3364,'lng':99.0628},
  {'nama':'PA Tarutung','jenis':'PA','alamat':'Jl. Raja Johannes Hutabarat No. 51, Tapanuli Utara','lat':1.9965,'lng':98.9764},
  {'nama':'PA Pandan','jenis':'PA','alamat':'Jl. D.I. Pandjaitan No. 4, Sibuluan Indah, Pandan','lat':1.6830,'lng':98.8270},
  {'nama':'PA Sibolga','jenis':'PA','alamat':'Jl. Perintis Kemerdekaan No. 1, Sibolga Kota','lat':1.7410,'lng':98.7758},
  {'nama':'PA Padangsidimpuan','jenis':'PA','alamat':'Kota Padangsidimpuan','lat':1.3786,'lng':99.2722},
  {'nama':'PA Kota Padangsidimpuan','jenis':'PA','alamat':'Jl. H.T. Rizal Nurdin Km 7, Salambue','lat':1.3786,'lng':99.2722},
  {'nama':'PA Panyabungan','jenis':'PA','alamat':'Panyabungan, Kab. Mandailing Natal','lat':0.8400,'lng':99.5550},
  {'nama':'PA Sibuhuan','jenis':'PA','alamat':'Jl. Ki Hajar Dewantara, Pasar Sibuhuan, Padang Lawas','lat':1.2335,'lng':99.7891},
  {'nama':'PA Rantauprapat (IB)','jenis':'PA','alamat':'Jl. SM. Raja, Komplek Asrama Haji No. 4, Rantauprapat','lat':2.1023,'lng':99.8247},
  {'nama':'PA Gunungsitoli','jenis':'PA','alamat':'Jl. Pancasila No. 29, Gunungsitoli, Nias','lat':1.2861,'lng':97.6169},
]

LAT_TOP = 6.6563341199999995
LAT_BOTTOM = -6.83691892
LNG_LEFT = 94.409464224
LNG_RIGHT = 107.208899776

markers_html = ''
list_html = ''

for idx, loc in enumerate(lokasi_peradilan):
    left_pct = ((loc['lng'] - LNG_LEFT) / (LNG_RIGHT - LNG_LEFT)) * 100
    top_pct = ((LAT_TOP - loc['lat']) / (LAT_TOP - LAT_BOTTOM)) * 100
    is_pta = loc['jenis'] == 'PTA'
    cls_pta = 'is-pta' if is_pta else ''
    
    markers_html += f'''
                    <div class="lp-discover-marker {cls_pta}" 
                         style="left: {left_pct}%; top: {top_pct}%;"
                         data-idx="{idx}"
                         onmouseenter="selectLocation({idx})"
                         onclick="selectLocation({idx})">
                        <div class="lp-marker-dot"></div>
                    </div>'''

    cls_item_pta = 'item-pta' if is_pta else ''
    icon_cls = 'ph-fill' if is_pta else 'ph-duotone'
    list_html += f'''
                        <div class="lp-loc-item {cls_item_pta}" id="loc-item-{idx}" onclick="selectLocation({idx})">
                            <div class="lp-loc-icon">
                                <i class="{icon_cls} ph-map-pin"></i>
                            </div>
                            <div class="lp-loc-text">
                                <strong>{loc['nama']}</strong>
                            </div>
                        </div>'''

html_section = f'''
    <!-- SECTION 6: PETA LOKASI -->
    <section id="peta" class="lp-section" style="background: #022c22; padding: 80px 0;">
        <div class="lp-container">
            <h2 class="lp-section-title" style="text-align: center; margin-bottom: 40px; color: #fff;">Jaringan Peradilan</h2>
            
            <div class="lp-map-discover">
                <div class="lp-discover-visual" id="map-container">
                    <img src="assets/peta_sumut.svg?v=7" alt="Peta Sumatera" class="lp-discover-bg">
                    {markers_html}
                    
                    <div id="map-info-card" class="lp-map-info-card">
                        <div class="info-card-header">
                            <span id="info-jenis" class="info-badge">PA</span>
                            <h4 id="info-nama">Nama PA</h4>
                        </div>
                        <p id="info-alamat">Alamat</p>
                    </div>
                </div>
                
                <div class="lp-discover-sidebar">
                    <div class="lp-sidebar-header">
                        <h3>Daftar Lokasi</h3>
                        <p>Wilayah Hukum Sumatera Utara</p>
                    </div>
                    <div class="lp-sidebar-list" id="sidebar-list-container">
{list_html}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        const lokasiData = {json.dumps(lokasi_peradilan)};
        
        function selectLocation(idx) {{
            document.querySelectorAll('.lp-discover-marker').forEach(el => el.classList.remove('active'));
            document.querySelectorAll('.lp-loc-item').forEach(el => el.classList.remove('active'));
            
            const marker = document.querySelector(`.lp-discover-marker[data-idx="${{idx}}"]`);
            const item = document.getElementById(`loc-item-${{idx}}`);
            
            if(marker) marker.classList.add('active');
            if(item) {{
                item.classList.add('active');
                const container = document.getElementById('sidebar-list-container');
                const itemTop = item.offsetTop;
                const itemBottom = itemTop + item.offsetHeight;
                const containerTop = container.scrollTop;
                const containerBottom = containerTop + container.offsetHeight;
                
                if (itemTop < containerTop || itemBottom > containerBottom) {{
                    item.scrollIntoView({{ behavior: 'smooth', block: 'nearest' }});
                }}
            }}
            
            const card = document.getElementById('map-info-card');
            const data = lokasiData[idx];
            document.getElementById('info-nama').textContent = data.nama;
            document.getElementById('info-alamat').textContent = data.alamat;
            document.getElementById('info-jenis').textContent = data.jenis;
            
            if(marker) {{
                card.style.display = 'block';
                card.style.left = marker.style.left;
                card.style.top = marker.style.top;
            }}
        }}
    </script>
</body>
</html>
'''

with open('preview.html', 'r', encoding='utf-8') as f:
    content = f.read()

start_idx = content.find('<!-- SECTION 6: PETA LOKASI -->')
if start_idx != -1:
    new_content = content[:start_idx] + html_section
    with open('preview.html', 'w', encoding='utf-8') as f:
        f.write(new_content)
    print('Updated preview.html')
else:
    print('Could not find SECTION 6')
