import { Network } from 'vis-network/standalone';
import { DataSet } from 'vis-data/standalone';

const root = document.getElementById('topology-map-root');

if (root) {
    const graphUrl = root.dataset.graphUrl;
    const container = document.getElementById('topology-network');
    const emptyState = document.getElementById('topology-empty');
    const detailPanel = document.getElementById('topology-detail');
    const detailBody = document.getElementById('topology-detail-body');
    const filtersForm = document.getElementById('topology-filters');
    const refreshButton = document.getElementById('topology-refresh');

    const groups = {
        up: { color: { background: '#10b981', border: '#059669' }, font: { color: '#ffffff' } },
        down: { color: { background: '#ef4444', border: '#dc2626' }, font: { color: '#ffffff' } },
        unknown_status: { color: { background: '#9ca3af', border: '#6b7280' }, font: { color: '#ffffff' } },
        unknown: { color: { background: '#64748b', border: '#475569' }, font: { color: '#ffffff' } },
    };

    let network = null;

    function buildQuery() {
        const params = new URLSearchParams();
        const locationId = document.getElementById('location_id')?.value;
        const vendor = document.getElementById('vendor')?.value;
        const registeredOnly = document.getElementById('registered_only')?.checked;

        if (locationId) {
            params.set('location_id', locationId);
        }

        if (vendor) {
            params.set('vendor', vendor);
        }

        if (registeredOnly) {
            params.set('registered_only', '1');
        }

        return params.toString();
    }

    async function loadGraph() {
        const query = buildQuery();
        const response = await fetch(`${graphUrl}?${query}`, {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            throw new Error('Gagal memuat data topologi.');
        }

        return response.json();
    }

    function renderGraph(data) {
        const hasEdges = Array.isArray(data.edges) && data.edges.length > 0;
        const hasNodes = Array.isArray(data.nodes) && data.nodes.length > 0;

        emptyState.classList.toggle('hidden', hasEdges || hasNodes);
        emptyState.classList.toggle('flex', !hasEdges && !hasNodes);

        const nodes = new DataSet(data.nodes.map((node) => ({
            ...node,
            shape: 'dot',
            size: 18,
        })));

        const edges = new DataSet(data.edges.map((edge) => ({
            ...edge,
            font: { align: 'middle', size: 10 },
            smooth: { type: 'dynamic' },
        })));

        const options = {
            groups,
            physics: {
                stabilization: true,
                barnesHut: {
                    gravitationalConstant: -4000,
                    springLength: 180,
                },
            },
            interaction: {
                hover: true,
                tooltipDelay: 150,
            },
        };

        if (network) {
            network.setData({ nodes, edges });
        } else {
            network = new Network(container, { nodes, edges }, options);

            network.on('click', (params) => {
                if (!params.nodes.length) {
                    detailPanel.classList.add('hidden');
                    return;
                }

                const node = nodes.get(params.nodes[0]);

                if (!node) {
                    return;
                }

                detailPanel.classList.remove('hidden');
                detailBody.innerHTML = `
                    <p><strong>${node.label}</strong></p>
                    <pre class="mt-2 whitespace-pre-wrap font-sans text-xs">${node.title ?? ''}</pre>
                    ${node.url ? `<a href="${node.url}" class="mt-3 inline-block text-indigo-600 hover:text-indigo-800">Buka detail perangkat →</a>` : '<p class="mt-3 text-xs text-gray-500">Neighbor belum terdaftar di inventori NMS.</p>'}
                `;
            });

            network.on('doubleClick', (params) => {
                if (!params.nodes.length) {
                    return;
                }

                const node = nodes.get(params.nodes[0]);

                if (node?.url) {
                    window.location.href = node.url;
                }
            });
        }
    }

    async function refreshGraph() {
        try {
            const data = await loadGraph();
            renderGraph(data);
        } catch (error) {
            emptyState.textContent = error.message;
            emptyState.classList.remove('hidden');
            emptyState.classList.add('flex');
        }
    }

    refreshButton?.addEventListener('click', refreshGraph);
    filtersForm?.addEventListener('change', refreshGraph);

    refreshGraph();
}
