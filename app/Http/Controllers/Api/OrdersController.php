<?php

namespace App\Http\Controllers\Api;

use App\Models\Orders;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrdersRequest;
use App\Http\Requests\UpdateOrdersRequest;
use App\Interfaces\OrderRepositoryInterface;
use Illuminate\Http\jsonResponse;

class OrdersController extends Controller
{
    private OrderRepositoryInterface $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    /**
     * Get all orders
     * GET /api/orders
     */
    public function index()
    {
        try {
            $orders = $this->orderRepository->getAllOrders();
            return response()->json([
                'success' => true,
                'message' => 'Orders retrieved successfully',
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new order with items
     * POST /api/orders
     */
    public function store(StoreOrdersRequest $request)
    {
        try {
            $order = $this->orderRepository->createOrder($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
                'data' => $order
            ], 201); // 201 Created
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single order detail
     * GET /api/orders/{id}
     */
    public function show(Orders $orders)
    {
        try {
            $order = $this->orderRepository->getOrderById($orders->id);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order retrieved successfully',
                'data' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update order information
     * PUT /api/orders/{id}
     */
    public function update(UpdateOrdersRequest $request, Orders $orders)
    {
        try {
            $order = $this->orderRepository->updateOrder($orders->id, $request->validated());

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully',
                'data' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete order and restore stock
     * DELETE /api/orders/{id}
     */
    public function destroy(Orders $orders)
    {
        try {
            $deleted = $this->orderRepository->deleteOrder($orders->id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get order with detailed items
     * GET /api/orders/{id}/items
     */
    public function getOrderWithItems(int $orderId): JsonResponse
    {
        try {
            $order = $this->orderRepository->getOrderWithItems($orderId);

            if (!$order) {
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Order with items retrieved successfully',
                'data' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve order with items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finalize order (mark as completed and paid)
     * POST /api/orders/{id}/finalize
     */
    public function finalizeOrder(int $orderId): JsonResponse
    {
        try {
            $this->orderRepository->finalize($orderId);
            return response()->json([
                'success' => true,
                'message' => 'Order finalized successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to finalize order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
